<?php
namespace WebhookHandlers;

use \Models\Member;
use \Models\Mandate;

class SubscriptionCreatedHandler extends AbstractWebhookHandler {

    /**
     * Handle subscriptions.created event
     * @param object $event Event object from GoCardless library
     * @param \Models\WebhookLog $webhook_log
     * @return array
     */
    public function handle($event, $webhook_log) {
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
        $subscription_type = $subscription->name ?? 'Unknown';
        $membershiptype_id = $this->getMembershipStatusFromSubscriptionType($subscription_type);
        $multiplier = $this->getDefaultMultiplierFromSubscriptionType($subscription_type);

        // Step 2: Fetch mandate details from GoCardless API, to get customer ID
        $mandate = $this->getMandateDetails($mandate_id);
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
            throw new \Exception("Failed to fetch member from database: " . $e->getMessage());
        }

        // Step 4: Add mandate details to database
        try {
            $mandate_model = new Mandate();
            $mandate_model->idmember = $member_id;
            $mandate_model->gc_mandate_id = $mandate_id;
            $mandate_model->gc_customer_id = $customer_id;
            $mandate_model->gc_subscriptionid = $subscription_id;

            // check for duplicate record with same mandate ID
            if ($mandate_model->exists($mandate_id)) {
                error_log("Mandate record already exists for mandate ID: $mandate_id");

                if (!$mandate_model->update()) {
                    throw new \Exception('Failed to update mandate record in database');
                }

            } else {
                error_log("No existing mandate record found for mandate ID: $mandate_id, creating new record");

                if (!$mandate_model->create()) {
                    throw new \Exception('Failed to create mandate record in database');
                }
            }

            error_log("Created mandate record in database for member $member_id");
            
        } catch (\Exception $e) {
            throw new \Exception("Failed to create mandate record in database: " . $e->getMessage());
        }

        // Step 5: Update membership type and multiplier based on subscription type
        try {
            $member->statusID = $membershiptype_id;
            $member->multiplier = $multiplier;
            $member->update();
            error_log("Updated member $member_id membership status, multiplier");
        } catch (\Exception $e) {
            throw new \Exception("Failed to update member status: " . $e->getMessage());
        }

        // Mark as processed
        $webhook_log->markProcessed();

        return [
            'event_id' => $event->id,
            'status' => 'success',
            'member_id' => $member_id,
            'subscription_id' => $subscription_id
        ];
    }
}
