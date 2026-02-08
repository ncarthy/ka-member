<?php
namespace Models;
use \PDO;

class WebhookQueue {
    // database conn
    private $conn;
    // table name
    private $table_name = "webhook_queue";

    // object properties
    public $id;
    public $event_id;
    public $resource_type;
    public $action;
    public $payload;
    public $raw_payload;
    public $status;
    public $retry_count;
    public $max_retries;
    public $error_message;
    public $created_at;
    public $processing_at;
    public $completed_at;
    public $next_retry_at;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * Enqueue a webhook event for processing
     * @return bool
     */
    public function enqueue() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET event_id = :event_id,
                      resource_type = :resource_type,
                      action = :action,
                      payload = :payload,
                      raw_payload = :raw_payload,
                      status = 'pending',
                      retry_count = 0,
                      max_retries = :max_retries";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->event_id = htmlspecialchars(strip_tags($this->event_id));
        $this->resource_type = htmlspecialchars(strip_tags($this->resource_type));
        $this->action = htmlspecialchars(strip_tags($this->action));
        // Don't sanitize payload - it's JSON data
        $max_retries = $this->max_retries ?? 3;

        // bind values
        $stmt->bindParam(":event_id", $this->event_id);
        $stmt->bindParam(":resource_type", $this->resource_type);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":payload", $this->payload);
        $stmt->bindParam(":raw_payload", $this->raw_payload);
        $stmt->bindParam(":max_retries", $max_retries);

        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Check if event already exists in queue
     * @param string $event_id
     * @return bool
     */
    public function exists($event_id) {
        $query = "SELECT idwebhook_queue
                  FROM " . $this->table_name . "
                  WHERE event_id = :event_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $event_id = htmlspecialchars(strip_tags($event_id));
        $stmt->bindParam(":event_id", $event_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Fetch pending events ready for processing
     * @param int $limit Maximum number of events to fetch
     * @return array Array of pending events
     */
    public function fetchPendingEvents($limit = 10) {
        $query = "SELECT idwebhook_queue, event_id, resource_type, action,
                         payload, raw_payload, retry_count, max_retries
                  FROM " . $this->table_name . "
                  WHERE status = 'pending'
                    AND (next_retry_at IS NULL OR next_retry_at <= NOW())
                  ORDER BY created_at ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = [
                'id' => $row['idwebhook_queue'],
                'event_id' => $row['event_id'],
                'resource_type' => $row['resource_type'],
                'action' => $row['action'],
                'payload' => $row['payload'],
                'raw_payload' => $row['raw_payload'],
                'retry_count' => $row['retry_count'],
                'max_retries' => $row['max_retries']
            ];
        }

        return $events;
    }

    /**
     * Mark event as processing
     * @param int $id
     * @return bool
     */
    public function markAsProcessing($id) {
        $query = "UPDATE " . $this->table_name . "
                  SET status = 'processing',
                      processing_at = NOW()
                  WHERE idwebhook_queue = :id
                    AND status = 'pending'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Mark event as completed
     * @param int $id
     * @return bool
     */
    public function markAsCompleted($id) {
        $query = "UPDATE " . $this->table_name . "
                  SET status = 'completed',
                      completed_at = NOW(),
                      error_message = NULL
                  WHERE idwebhook_queue = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Mark event as failed and schedule retry if attempts remain
     * @param int $id
     * @param string $error_message
     * @return bool
     */
    public function markAsFailed($id, $error_message) {
        // Get current retry count
        $query = "SELECT retry_count, max_retries
                  FROM " . $this->table_name . "
                  WHERE idwebhook_queue = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $retry_count = $row['retry_count'];
        $max_retries = $row['max_retries'];

        $new_retry_count = $retry_count + 1;

        if ($new_retry_count >= $max_retries) {
            // Max retries reached - mark as permanently failed
            $update_query = "UPDATE " . $this->table_name . "
                           SET status = 'failed',
                               retry_count = :retry_count,
                               error_message = :error_message
                           WHERE idwebhook_queue = :id";

            $stmt = $this->conn->prepare($update_query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":retry_count", $new_retry_count, PDO::PARAM_INT);
            $error_message_clean = htmlspecialchars(strip_tags($error_message));
            $stmt->bindParam(":error_message", $error_message_clean);
            return $stmt->execute();
        } else {
            // Schedule retry with exponential backoff
            $retry_delay_minutes = pow(2, $new_retry_count); // 2, 4, 8 minutes

            $update_query = "UPDATE " . $this->table_name . "
                           SET status = 'pending',
                               retry_count = :retry_count,
                               error_message = :error_message,
                               next_retry_at = DATE_ADD(NOW(), INTERVAL :delay MINUTE)
                           WHERE idwebhook_queue = :id";

            $stmt = $this->conn->prepare($update_query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":retry_count", $new_retry_count, PDO::PARAM_INT);
            $error_message_clean = htmlspecialchars(strip_tags($error_message));
            $stmt->bindParam(":error_message", $error_message_clean);
            $stmt->bindParam(":delay", $retry_delay_minutes, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }

    /**
     * Get queue statistics
     * @return array
     */
    public function getStats() {
        $query = "SELECT
                    status,
                    COUNT(*) as count
                  FROM " . $this->table_name . "
                  GROUP BY status";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $stats = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = (int)$row['count'];
        }

        return $stats;
    }

    /**
     * Reset stuck processing events (been processing for more than X minutes)
     * @param int $timeout_minutes
     * @return int Number of events reset
     */
    public function resetStuckEvents($timeout_minutes = 30) {
        $query = "UPDATE " . $this->table_name . "
                  SET status = 'pending',
                      processing_at = NULL,
                      next_retry_at = NOW()
                  WHERE status = 'processing'
                    AND processing_at < DATE_SUB(NOW(), INTERVAL :timeout MINUTE)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":timeout", $timeout_minutes, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
