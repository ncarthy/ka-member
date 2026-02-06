<?php
namespace Models;
use \PDO;

class GoCardlessWebhook {
    private $conn;
    private $webhook_secret;

    // Constants for payment configuration
    const BANK_ID = 5; // Lloyds bank account (receives GoCardless payments)
    const PAYMENT_TYPE_ID = 6; // Direct Debit payment type

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
        $this->webhook_secret = getenv(\Core\Config::read('gocardless.webhook_secret'));

        if (empty($this->webhook_secret)) {
            error_log('GoCardless webhook secret not configured');
        }
    }

    /**
     * Validate webhook signature using HMAC-SHA256
     * @param string $payload Raw JSON payload
     * @param string $signature Signature from Webhook-Signature header
     * @return bool
     */
    public function validateSignature($payload, $signature) {
        if (empty($this->webhook_secret)) {
            error_log('Cannot validate signature: webhook secret not configured');
            return false;
        }

        $expected_signature = $signature;
        $computed_signature = hash_hmac('sha256', $payload, $this->webhook_secret);

        // Use timing-safe comparison to prevent timing attacks
        return hash_equals($computed_signature, $expected_signature);
    }

    /**
     * Process webhook events
     * @param array $webhook_data Decoded JSON webhook payload
     * @return array Results of processing each event
     */
    public function processEvents($webhook_data) {
        $results = [];

        if (!isset($webhook_data['events']) || !is_array($webhook_data['events'])) {
            error_log('Invalid webhook data: no events array');
            return ['error' => 'Invalid webhook data'];
        }

        foreach ($webhook_data['events'] as $event) {
            $result = $this->processEvent($event);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Process a single event
     * @param array $event Event data
     * @return array Result of processing
     */
    private function processEvent($event) {
        $event_id = $event['id'] ?? 'unknown';
        $resource_type = $event['resource_type'] ?? '';
        $action = $event['action'] ?? '';

        error_log("Processing event: $event_id - $resource_type.$action");

        // Log webhook to database
        $webhook_log = new WebhookLog();
        $webhook_log->webhook_id = $event_id; // Using event ID as webhook ID for idempotency
        $webhook_log->event_id = $event_id;
        $webhook_log->resource_type = $resource_type;
        $webhook_log->action = $action;
        $webhook_log->resource_id = $event['links'][$resource_type] ?? null;
        $webhook_log->payload = json_encode($event);

        // Check idempotency
        if ($webhook_log->exists($event_id)) {
            error_log("Event $event_id already processed - skipping");
            return ['event_id' => $event_id, 'status' => 'duplicate'];
        }

        // Create log entry
        if (!$webhook_log->create()) {
            error_log("Failed to create webhook log for event $event_id");
            return ['event_id' => $event_id, 'status' => 'log_failed'];
        }

        // Route to appropriate handler
        try {
            if ($resource_type === 'mandates' && $action === 'created') {
                $result = $this->handleMandateCreated($event, $webhook_log);
            } elseif ($resource_type === 'payments' && $action === 'confirmed') {
                $result = $this->handlePaymentConfirmed($event, $webhook_log);
            } else {
                error_log("Unhandled event type: $resource_type.$action");
                $webhook_log->markProcessed();
                $result = ['event_id' => $event_id, 'status' => 'unhandled'];
            }

            return $result;
        } catch (\Exception $e) {
            error_log("Error processing event $event_id: " . $e->getMessage());
            $webhook_log->recordError($e->getMessage());
            return ['event_id' => $event_id, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle mandates.created event - creates new member record
     * @param array $event Event data
     * @param WebhookLog $webhook_log
     * @return array
     */
    private function handleMandateCreated($event, $webhook_log) {
        $mandate_id = $event['links']['mandate'] ?? null;
        $metadata = $event['metadata'] ?? [];

        if (empty($mandate_id)) {
            throw new \Exception('Missing mandate ID in event');
        }

        // Extract member details from metadata
        $member_name = $metadata['member_name'] ?? null;
        $business_name = $metadata['business_name'] ?? null;
        $email = $metadata['email'] ?? null;
        $mandate_type = $metadata['mandate_type'] ?? 'individual';

        // Extract address details from metadata
        $address_line1 = $metadata['address_line1'] ?? null;
        $address_line2 = $metadata['address_line2'] ?? null;
        $city = $metadata['city'] ?? null;
        $county = $metadata['county'] ?? null;
        $postcode = $metadata['postcode'] ?? null;

        // Determine display name (business name takes precedence)
        $display_name = $business_name ?? $member_name;

        if (empty($display_name)) {
            throw new \Exception('Missing member name or business name in metadata');
        }

        if (empty($email)) {
            throw new \Exception('Missing email in metadata');
        }

        // Get membership status from mandate type
        $membership_status_id = $this->getMembershipStatusFromMandateType($mandate_type);

        if ($membership_status_id === null) {
            throw new \Exception("Unknown mandate type: $mandate_type");
        }

        // Create new member record
        $query = "INSERT INTO member
                  SET businessname = :businessname,
                      bankpayerref = :bankpayerref,
                      email1 = :email1,
                      addressfirstline = :addressfirstline,
                      addresssecondline = :addresssecondline,
                      city = :city,
                      county = :county,
                      postcode = :postcode,
                      joindate = CURDATE(),
                      username = 'gocardless_webhook',
                      membership_idmembership = :membership_status_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $display_name = htmlspecialchars(strip_tags($display_name));
        $mandate_id = htmlspecialchars(strip_tags($mandate_id));
        $email = htmlspecialchars(strip_tags($email));
        $address_line1 = htmlspecialchars(strip_tags($address_line1 ?? ''));
        $address_line2 = htmlspecialchars(strip_tags($address_line2 ?? ''));
        $city = htmlspecialchars(strip_tags($city ?? ''));
        $county = htmlspecialchars(strip_tags($county ?? ''));
        $postcode = htmlspecialchars(strip_tags($postcode ?? ''));

        // Bind parameters
        $stmt->bindParam(":businessname", $display_name);
        $stmt->bindParam(":bankpayerref", $mandate_id);
        $stmt->bindParam(":email1", $email);
        $stmt->bindParam(":addressfirstline", $address_line1);
        $stmt->bindParam(":addresssecondline", $address_line2);
        $stmt->bindParam(":city", $city);
        $stmt->bindParam(":county", $county);
        $stmt->bindParam(":postcode", $postcode);
        $stmt->bindParam(":membership_status_id", $membership_status_id);

        if ($stmt->execute()) {
            $member_id = $this->conn->lastInsertId();
            error_log("Created member ID $member_id with mandate $mandate_id");

            // Mark webhook as processed with member ID
            $webhook_log->markProcessed($member_id);

            return [
                'event_id' => $event['id'],
                'status' => 'success',
                'member_id' => $member_id,
                'mandate_id' => $mandate_id
            ];
        } else {
            throw new \Exception('Failed to create member record');
        }
    }

    /**
     * Handle payments.confirmed event - creates transaction record
     * @param array $event Event data
     * @param WebhookLog $webhook_log
     * @return array
     */
    private function handlePaymentConfirmed($event, $webhook_log) {
        $payment_id = $event['links']['payment'] ?? null;
        $mandate_id = $event['links']['mandate'] ?? null;
        $amount_pence = $event['details']['amount'] ?? null;
        $currency = $event['details']['currency'] ?? 'GBP';

        if (empty($payment_id) || empty($mandate_id) || $amount_pence === null) {
            throw new \Exception('Missing required payment data');
        }

        // Find member by mandate ID (bankpayerref)
        $member_query = "SELECT idmember
                         FROM member
                         WHERE bankpayerref = :mandate_id
                         LIMIT 1";

        $stmt = $this->conn->prepare($member_query);
        $mandate_id_clean = htmlspecialchars(strip_tags($mandate_id));
        $stmt->bindParam(":mandate_id", $mandate_id_clean);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new \Exception("No member found with mandate ID: $mandate_id");
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $member_id = $row['idmember'];

        // Check for duplicate transaction
        $dup_check_query = "SELECT idtransaction
                            FROM transaction
                            WHERE note LIKE :payment_ref
                            AND member_idmember = :member_id
                            LIMIT 1";

        $stmt = $this->conn->prepare($dup_check_query);
        $payment_ref = "%$payment_id%";
        $stmt->bindParam(":payment_ref", $payment_ref);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            error_log("Transaction for payment $payment_id already exists - skipping");
            $webhook_log->markProcessed($member_id);
            return [
                'event_id' => $event['id'],
                'status' => 'duplicate_transaction',
                'payment_id' => $payment_id
            ];
        }

        // Convert amount from pence to pounds
        $amount_pounds = $amount_pence / 100;

        // Create transaction note
        $note = "GoCardless payment $payment_id";

        // Insert transaction
        $trans_query = "INSERT INTO transaction
                        SET date = CURDATE(),
                            amount = :amount,
                            note = :note,
                            member_idmember = :member_id,
                            bankID = :bank_id,
                            paymenttypeID = :payment_type_id";

        $stmt = $this->conn->prepare($trans_query);
        $note_clean = htmlspecialchars(strip_tags($note));
        $stmt->bindParam(":amount", $amount_pounds);
        $stmt->bindParam(":note", $note_clean);
        $stmt->bindParam(":member_id", $member_id);
        $bank_id = self::BANK_ID;
        $payment_type_id = self::PAYMENT_TYPE_ID;
        $stmt->bindParam(":bank_id", $bank_id);
        $stmt->bindParam(":payment_type_id", $payment_type_id);

        if ($stmt->execute()) {
            $transaction_id = $this->conn->lastInsertId();
            error_log("Created transaction ID $transaction_id for payment $payment_id (member $member_id)");

            // Mark webhook as processed
            $webhook_log->markProcessed($member_id);

            return [
                'event_id' => $event['id'],
                'status' => 'success',
                'transaction_id' => $transaction_id,
                'payment_id' => $payment_id,
                'member_id' => $member_id,
                'amount' => $amount_pounds
            ];
        } else {
            throw new \Exception('Failed to create transaction record');
        }
    }

    /**
     * Map mandate type to membership status ID
     * @param string $mandate_type
     * @return int|null Membership status ID or null if unknown
     */
    private function getMembershipStatusFromMandateType($mandate_type) {
        // Mapping of mandate types to membership status IDs
        $mapping = [
            'individual' => 2,   // Individual Member
            'household' => 3,    // Household Member
            'corporate' => 4,    // Corporate Member
            'lifetime' => 5,     // Lifetime Member
        ];

        if (!isset($mapping[$mandate_type])) {
            error_log("Unknown mandate type: $mandate_type");
            return null;
        }

        return $mapping[$mandate_type];
    }
}
