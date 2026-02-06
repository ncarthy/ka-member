<?php
namespace Models;
use \PDO;

class WebhookLog {
    // database conn
    private $conn;
    // table name
    private $table_name = "webhook_log";

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    // object properties
    public $idwebhook_log;
    public $webhook_id;
    public $event_id;
    public $resource_type;
    public $action;
    public $resource_id;
    public $payload;
    public $processed;
    public $idmember;
    public $error_message;
    public $created_at;
    public $processed_at;

    /**
     * Check if webhook_id already exists (idempotency check)
     * @param string $webhook_id
     * @return bool
     */
    public function exists($webhook_id) {
        $query = "SELECT idwebhook_log
                  FROM " . $this->table_name . "
                  WHERE webhook_id = :webhook_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $webhook_id = htmlspecialchars(strip_tags($webhook_id));
        $stmt->bindParam(":webhook_id", $webhook_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Create a new webhook log entry
     * @return bool
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET webhook_id = :webhook_id,
                      event_id = :event_id,
                      resource_type = :resource_type,
                      action = :action,
                      resource_id = :resource_id,
                      payload = :payload,
                      processed = 0";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->webhook_id = htmlspecialchars(strip_tags($this->webhook_id));
        $this->event_id = htmlspecialchars(strip_tags($this->event_id));
        $this->resource_type = htmlspecialchars(strip_tags($this->resource_type));
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->resource_id = htmlspecialchars(strip_tags($this->resource_id ?? ''));
        // Don't use htmlspecialchars on JSON payload to preserve quotes
        $this->payload = strip_tags($this->payload);

        // bind
        $stmt->bindParam(":webhook_id", $this->webhook_id);
        $stmt->bindParam(":event_id", $this->event_id);
        $stmt->bindParam(":resource_type", $this->resource_type);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":resource_id", $this->resource_id);
        $stmt->bindParam(":payload", $this->payload);

        if($stmt->execute()){
            $this->idwebhook_log = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Mark webhook as processed
     * @param int $idmember Optional member ID to associate
     * @return bool
     */
    public function markProcessed($idmember = null) {
        $query = "UPDATE " . $this->table_name . "
                  SET processed = 1,
                      processed_at = NOW()";

        if ($idmember !== null) {
            $query .= ", idmember = :idmember";
        }

        $query .= " WHERE idwebhook_log = :idwebhook_log";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":idwebhook_log", $this->idwebhook_log);

        if ($idmember !== null) {
            $stmt->bindParam(":idmember", $idmember);
        }

        return $stmt->execute();
    }

    /**
     * Record error for webhook processing
     * @param string $error_message
     * @return bool
     */
    public function recordError($error_message) {
        $query = "UPDATE " . $this->table_name . "
                  SET error_message = :error_message,
                      processed = 0
                  WHERE idwebhook_log = :idwebhook_log";

        $stmt = $this->conn->prepare($query);
        $error_message = htmlspecialchars(strip_tags($error_message));
        $stmt->bindParam(":error_message", $error_message);
        $stmt->bindParam(":idwebhook_log", $this->idwebhook_log);

        return $stmt->execute();
    }
}
