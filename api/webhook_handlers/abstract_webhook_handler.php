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
            'ka lifetime membership' => 5,     // Lifetime Member
            'ka honorary membership' => 6,     // Honorary Member
        ];

        $lower_case_type = strtolower($subscription_type);

        if (!isset($mapping[$lower_case_type])) {
            error_log("Unknown subscription type: $subscription_type");
            return null;
        }

        return $mapping[$lower_case_type];
    }

    /**
     * Helper method to get default membership multiplier from subscription type.
     * Different membership classes have different contributions to the membership total. Each 
     * household member counts as 2 individual members, a corporate member counts as 4, an 
     * individual member counts as 1. 
     * @param string $subscription_type
     * @return int Default to 1 if unknown type
     */
    protected function getDefaultMultiplierFromSubscriptionType($subscription_type) {
        $mapping = [
            'ka individual membership' => 1,   // Individual Member
            'ka household membership' => 2,    // Household Member
            'ka corporate membership' => 4,    // Corporate Member
            'ka lifetime membership' => 1,     // Lifetime Member
            'ka honorary membership' => 1,     // Honorary Member
        ];

        $lower_case_type = strtolower($subscription_type);

        if (!isset($mapping[$lower_case_type])) {
            error_log("Unknown subscription type: $subscription_type");
            return 1; // Default to 1 if unknown type
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

        /**
     * Helper method to get Payment details from GoCardless API
     * @param mixed $payment_id 
     * @return mixed 
     * @throws mixed 
     */
    protected function getPaymentDetails($payment_id) {
        try {
            $payment = $this->client->payments()->get($payment_id);
            error_log("Fetched payment $payment_id from GoCardless API");
            return $payment;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch payment from GoCardless API: " . $e->getMessage());
        }
    }

        /**
     * Helper method to get Subscription details from GoCardless API
     * @param mixed $subscription_id 
     * @return mixed 
     * @throws mixed 
     */
    protected function getSubscriptionDetails($subscription_id) {
        try {
            $subscription = $this->client->subscriptions()->get($subscription_id);
            error_log("Fetched subscription $subscription_id from GoCardless API");
            return $subscription;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch subscription from GoCardless API: " . $e->getMessage());
        }
    }    
}
