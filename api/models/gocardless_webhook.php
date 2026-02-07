<?php
namespace Models;
use \PDO;
use \GoCardlessPro\Webhook;
use \GoCardlessPro\Core\Exception\InvalidSignatureException;
use \Models\MemberName;
use \Models\Member;
use \Models\Subscription;

class GoCardlessWebhook {
    private $conn;
    private $webhook_secret;
    private $client;

    // Constants for payment configuration
    const BANK_ID = 5; // Lloyds bank account (receives GoCardless payments)
    const PAYMENT_TYPE_ID = 6; // Direct Debit payment type
    const PENDIND_MEMBERSHIP_STATUS_ID = 7; // Pending membership status ID

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
        $this->webhook_secret = getenv(\Core\Config::read('gocardless.webhook_secret'));
        $access_token = getenv(\Core\Config::read('gocardless.access_token'));
        $this->client = new \GoCardlessPro\Client([
            'access_token' => $access_token,
            'environment'  => \GoCardlessPro\Environment::SANDBOX
        ]);

        if (empty($this->webhook_secret)) {
            error_log('GoCardless webhook secret not configured');
        }
    }

    /**
     * Parse and validate webhook using GoCardless library
     * @param string $payload Raw JSON payload
     * @param string $signature Signature from Webhook-Signature header
     * @return array Parsed events
     * @throws InvalidSignatureException if signature is invalid
     */
    public function parseWebhook($payload, $signature) {
        if (empty($this->webhook_secret)) {
            throw new \Exception('Webhook secret not configured');
        }

        // Use GoCardless library to parse and validate webhook
        return Webhook::parse($payload, $signature, $this->webhook_secret);
    }

    /**
     * Process webhook events
     * @param array $events Array of event objects from GoCardless library
     * @param string $raw_payload Original raw JSON payload for logging
     * @return array Results of processing each event
     */
    public function processEvents($events, $raw_payload) {
        $results = [];

        if (!is_array($events)) {
            error_log('Invalid events data: not an array');
            return ['error' => 'Invalid events data'];
        }

        // Parse raw payload to get original event data
        $payload_data = json_decode($raw_payload, true);
        $payload_events = $payload_data['events'] ?? [];

        foreach ($events as $index => $event) {
            // Get corresponding raw event data for this event
            $raw_event = $payload_events[$index] ?? null;
            $result = $this->processEvent($event, $raw_event);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Process a single event
     * @param object $event Event object from GoCardless library
     * @param array|null $raw_event Original raw event data from webhook payload
     * @return array Result of processing
     */
    private function processEvent($event, $raw_event = null) {
        // Access properties from GoCardless event object
        $event_id = $event->id ?? 'unknown';
        $resource_type = $event->resource_type ?? '';
        $action = $event->action ?? '';

        error_log("Processing event: $event_id - $resource_type.$action");

        // Log webhook to database
        $webhook_log = new WebhookLog();
        $webhook_log->webhook_id = $event_id; // Using event ID as webhook ID for idempotency
        $webhook_log->event_id = $event_id;
        $webhook_log->resource_type = $resource_type;
        $webhook_log->action = $action;
        $webhook_log->resource_id = $event->links->{$resource_type} ?? null;
        // Store the original raw event data instead of trying to serialize the object
        $webhook_log->payload = $raw_event ? json_encode($raw_event) : json_encode(['event_id' => $event_id]);

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
            } elseif ($resource_type === 'subscriptions' && $action === 'created') {
                $result = $this->handleSubscriptionCreated($event, $webhook_log);
            } elseif ($resource_type === 'subscriptions' && in_array($action, ['cancelled', 'failed', 'expired'])) {
                $result = $this->handleSubscriptionTerminated($event, $webhook_log);
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
     * Fetches mandate and customer data from GoCardless API
     * @param object $event Event object from GoCardless library
     * @param WebhookLog $webhook_log
     * @return array
     */
    private function handleMandateCreated($event, $webhook_log) {
        $mandate_id = $event->links->mandate ?? null;

        if (empty($mandate_id)) {
            throw new \Exception('Missing mandate ID in event');
        }

        // Step 1: Fetch mandate details from GoCardless API
        try {
            $mandate = $this->client->mandates()->get($mandate_id);
            error_log("Fetched mandate $mandate_id from GoCardless API");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch mandate from GoCardless API: " . $e->getMessage());
        }

        // Step 2: Get customer ID from mandate and fetch customer details
        $customer_id = $mandate->links->customer ?? null;

        if (empty($customer_id)) {
            throw new \Exception('Missing customer ID in mandate data');
        }

        try {
            $customer = $this->client->customers()->get($customer_id);
            error_log("Fetched customer $customer_id from GoCardless API for mandate $mandate_id");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch customer from GoCardless API: " . $e->getMessage());
        }

        // Extract customer details
        $given_name = $customer->given_name ?? '';
        $family_name = $customer->family_name ?? '';
        $company_name = $customer->company_name ?? '';
        $email = $customer->email ?? '';

        // Determine display name (company name takes precedence, then full name)
        if (!empty($company_name)) {
            $display_name = $company_name;
        } else {
            $display_name = trim("$given_name $family_name");
        }

        if (empty($company_name) && empty($display_name)) {
            throw new \Exception('Missing customer name and/or Business Name in GoCardless data');
        }

        if (empty($email)) {
            throw new \Exception('Missing email in GoCardless customer data');
        }

        // Extract address from customer
        $address_line1 = $customer->address_line1 ?? '';
        $address_line2 = $customer->address_line2 ?? '';
        $city = $customer->city ?? '';
        $county = $customer->region ?? ''; // GoCardless uses 'region' for county/state
        $postcode = $customer->postal_code ?? '';

        // Create new member record using Member model
        $member = new Member();

        // Set required fields
        $member->businessname = $display_name;
        $member->bankpayerref = $mandate_id;
        $member->email1 = $email;
        $member->addressfirstline = $address_line1;
        $member->addresssecondline = $address_line2;
        $member->city = $city;
        $member->county = $county;
        $member->postcode = $postcode;
        $member->joindate = date('Y-m-d'); // CURDATE() equivalent
        $member->username = 'gocardless_webhook';
        $member->statusID = self::PENDIND_MEMBERSHIP_STATUS_ID;

        // Set default values for other fields
        $member->title = '';
        $member->note = '';
        $member->countryID = 0;
        $member->area = '';
        $member->phone1 = '';
        $member->addressfirstline2 = '';
        $member->addresssecondline2 = '';
        $member->city2 = '';
        $member->county2 = '';
        $member->postcode2 = '';
        $member->country2ID = 0;
        $member->email2 = '';
        $member->phone2 = '';
        $member->expirydate = null;
        $member->reminderdate = null;
        $member->deletedate = null;
        $member->repeatpayment = 0;
        $member->recurringpayment = 0;
        $member->gdpr_email = false;
        $member->gdpr_tel = false;
        $member->gdpr_address = false;
        $member->gdpr_sm = false;
        $member->postonhold = false;
        $member->emailonhold = false;
        $member->multiplier = 1;
        $member->membershipfee = 0;
        $member->gpslat1 = null;
        $member->gpslat2 = null;
        $member->gpslng1 = null;
        $member->gpslng2 = null;

        if ($member->create()) {
            $member_id = $member->id;
            error_log("Created member ID $member_id with mandate $mandate_id");

            // Add member name if given name and family name are provided
            if (!empty($given_name) && !empty($family_name)) {
                $memberName = new MemberName();
                $memberName->honorific = ''; // No honorific from GoCardless
                $memberName->firstname = $given_name;
                $memberName->surname = $family_name;
                $memberName->idmember = $member_id;

                if ($memberName->create()) {
                    error_log("Created member name (ID: {$memberName->id}) for member $member_id: $given_name $family_name");
                } else {
                    error_log("Warning: Failed to create member name for member $member_id");
                    // Don't fail the whole operation if member name creation fails
                }
            }

            // Mark webhook as processed with member ID
            $webhook_log->markProcessed($member_id);

            return [
                'event_id' => $event->id,
                'status' => 'success',
                'member_id' => $member_id,
                'mandate_id' => $mandate_id
            ];
        } else {
            throw new \Exception('Failed to create member record using Member model');
        }
    }

    /**
     * Handle payments.confirmed event - creates transaction record
     * @param object $event Event object from GoCardless library
     * @param WebhookLog $webhook_log
     * @return array
     */
    private function handlePaymentConfirmed($event, $webhook_log) {
        $payment_id = $event->links->payment ?? null;
        $mandate_id = $event->links->mandate ?? null;
        $amount_pence = $event->details->amount ?? null;

        if (empty($payment_id) || empty($mandate_id) || $amount_pence === null) {
            throw new \Exception('Missing required payment data');
        }

        // Find member by mandate ID (bankpayerref)
        $member_query = "SELECT idmember
                         FROM member
                         WHERE bankpayerref = :mandate_id
                         LIMIT 1";

        $stmt = $this->conn->prepare($member_query);

        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new \Exception("Failed to prepare member lookup statement: " . $errorInfo[2]);
        }

        $mandate_id_clean = htmlspecialchars(strip_tags($mandate_id));
        $stmt->bindParam(":mandate_id", $mandate_id_clean);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new \Exception("Failed to lookup member: " . $errorInfo[2]);
        }

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

        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new \Exception("Failed to prepare duplicate check statement: " . $errorInfo[2]);
        }

        $payment_ref = "%$payment_id%";
        $stmt->bindParam(":payment_ref", $payment_ref);
        $stmt->bindParam(":member_id", $member_id);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new \Exception("Failed to check for duplicate transaction: " . $errorInfo[2]);
        }

        if ($stmt->rowCount() > 0) {
            error_log("Transaction for payment $payment_id already exists - skipping");
            $webhook_log->markProcessed($member_id);
            return [
                'event_id' => $event->id,
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

        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new \Exception("Failed to prepare transaction insert statement: " . $errorInfo[2]);
        }

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
                'event_id' => $event->id,
                'status' => 'success',
                'transaction_id' => $transaction_id,
                'payment_id' => $payment_id,
                'member_id' => $member_id,
                'amount' => $amount_pounds
            ];
        } else {
            // Get detailed error information
            $errorInfo = $stmt->errorInfo();
            $errorMsg = "Failed to create transaction record - " . $errorInfo[2];
            error_log($errorMsg);
            throw new \Exception($errorMsg);
        }
    }

    /**
     * Map subscription type to membership status ID
     * @param string $subscription_type
     * @return int|null Membership status ID or null if unknown
     */
    private function getMembershipStatusFromSubscriptionType($subscription_type) {
        // Mapping of subscription types to membership status IDs
        $mapping = [
            'ka corporate membership' => 2,   // Individual Member
            'ka household membership' => 3,    // Household Member
            'ka corporate membership' => 4,    // Corporate Member
        ];

        if (!isset($mapping[$subscription_type])) {
            error_log("Unknown subscription type: $subscription_type");
            return null;
        }

        return $mapping[$subscription_type];
    }

    /**
     * Handle subscriptions.created event
     * @param object $event Event object from GoCardless library
     * @param WebhookLog $webhook_log
     * @return array
     */
    private function handleSubscriptionCreated($event, $webhook_log) {
        

        $subscription_id = $event->links->subscription ?? null;

        if (empty($subscription_id)) {
            throw new \Exception('Missing subscription ID in event');
        }

        // Step 1: Fetch subscription details from GoCardless API
        try {
            $subscription = $this->client->subscriptions()->get($subscription_id);
            error_log("Fetched subscription $subscription_id from GoCardless API");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch subscription from GoCardless API: " . $e->getMessage());
        }

        // Extract subscription details
        $mandate_id = $subscription->links->mandate ?? '';
        $subscription_type = strtolower($subscription->name ?? 'unknown');
        $membershiptype_id = $this->getMembershipStatusFromSubscriptionType($subscription_type);

        // Step 2: Fetch mandate details from GoCardless API
        try {
            $mandate = $this->client->mandates()->get($mandate_id);
            error_log("Fetched mandate $mandate_id from GoCardless API");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch mandate from GoCardless API: " . $e->getMessage());
        }

        // Extract mandate details
        $customer_id = $mandate->links->customer ?? '';
        if (empty($customer_id)) {
            throw new \Exception('Missing customer ID in mandate');
        }

        // Step 3: Fetch member details from database
        try {            
            $member = new Member();
            $member->findByMandateId($mandate_id);
            $member_id = $member->id ?? null;
            if (empty($member_id)) {
                throw new \Exception("No member found with mandate ID: $mandate_id");
            }
            error_log("Fetched member $member_id from database");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch subscription from database: " . $e->getMessage());
        }

        // Step 4: Add subscription details to database
        try {
            $subscription_model = new Subscription();
            $subscription_model->idmember = $member_id;
            $subscription_model->gc_mandate_id = $mandate_id;
            $subscription_model->gc_customer_id = $customer_id;
            $subscription_model->gc_subscriptionid = ''; // Will update with actual subscription ID after saving
            if (!$subscription_model->create()) {
                throw new \Exception('Failed to create subscription record in database');
            }
            
            error_log("Created subscription record in database for member $member_id");
        } catch (\Exception $e) {
            throw new \Exception("Failed to create subscription record in database: " . $e->getMessage());
        }

        $member_id = $subscription_model->idmember ?? null;
        if (empty($member_id)) {
            throw new \Exception('Missing member ID in subscription model');
        }

        // Step 5:Update membership type based on subscription type
        try {            
            $member->statusID = $membershiptype_id;
            $member->update();
            error_log("Updated member $member_id membership status to $membershiptype_id");
        } catch (\Exception $e) {
            throw new \Exception("Failed to update member status: " . $e->getMessage());
        }

        // Mark as processed for now
        $webhook_log->markProcessed();

        return [
            'event_id' => $event->id,
            'status' => 'success',
            'member_id' => $member_id,
            'subscription_id' => $subscription_id
        ];
    }

    /**
     * Handle subscription termination events (cancelled, failed, expired)
     * @param object $event Event object from GoCardless library
     * @param WebhookLog $webhook_log
     * @return array
     */
    private function handleSubscriptionTerminated($event, $webhook_log) {
        // TODO: Implement subscription terminated logic

        $action = $event->action ?? '';

        $subscription_id = $event->links->subscription ?? null;

        if (empty($subscription_id)) {
            throw new \Exception('Missing subscription ID in event');
        }

        // Step 1: Fetch subscription details from GoCardless API
        try {
            $subscription = $this->client->subscriptions()->get($subscription_id);
            error_log("Fetched subscription $subscription_id from GoCardless API");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch subscription from GoCardless API: " . $e->getMessage());
        }

        // Mark as processed for now
        $webhook_log->markProcessed();

        return [
            'event_id' => $event->id,
            'status' => 'success',
            'action' => $action,
            'subscription_id' => $subscription_id,
            'message' => 'Subscription termination handler not yet implemented'
        ];
    }
}
