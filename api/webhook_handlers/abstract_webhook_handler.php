<?php
namespace WebhookHandlers;

abstract class AbstractWebhookHandler {
    protected $conn;
    protected $client;

    public function __construct($conn, $client) {
        $this->conn = $conn;
        $this->client = $client;
    }

    /**
     * Handle the webhook event
     * @param object $event Event object from GoCardless library
     * @param \Models\WebhookLog $webhook_log
     * @return array Result of processing
     */
    abstract public function handle($event, $webhook_log);

    /**
     * Helper method to get membership status from subscription type
     * @param string $subscription_type
     * @return int|null
     */
    protected function getMembershipStatusFromSubscriptionType($subscription_type) {
        $mapping = [
            'ka individual membership' => 2,   // Individual Member
            'ka household membership' => 3,    // Household Member
            'ka corporate membership' => 4,    // Corporate Member
        ];

        $lower_case_type = strtolower($subscription_type);

        if (!isset($mapping[$lower_case_type])) {
            error_log("Unknown subscription type: $subscription_type");
            return null;
        }

        return $mapping[$lower_case_type];
    }

    /**
     * Helper method to get Mandate details from GoCardless API
     * @param mixed $mandate_id 
     * @return mixed 
     * @throws mixed 
     */
    protected function getMandateDetails($mandate_id) {
        try {
            $mandate = $this->client->mandates()->get($mandate_id);
            error_log("Fetched mandate $mandate_id from GoCardless API");
            return $mandate;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch mandate from GoCardless API: " . $e->getMessage());
        }
    }

    /**
     * Helper method to get Customer details from GoCardless API
     * @param mixed $customer_id 
     * @return mixed 
     * @throws mixed 
     */
    protected function getCustomerDetails($customer_id) {
        try {
            $customer = $this->client->customers()->get($customer_id);
            error_log("Fetched customer $customer_id from GoCardless API");
            return $customer;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch customer from GoCardless API: " . $e->getMessage());
        }
    }
}
