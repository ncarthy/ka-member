<?php
namespace WebhookHandlers;

use \Models\Member;
use \Models\Subscription;

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

        // Step 4: Add subscription details to database
        try {
            $subscription_model = new Subscription();
            $subscription_model->idmember = $member_id;
            $subscription_model->gc_mandate_id = $mandate_id;
            $subscription_model->gc_customer_id = $customer_id;
            $subscription_model->gc_subscriptionid = $subscription_id;

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

        // Step 5: Update membership type based on subscription type
        try {
            $member->statusID = $membershiptype_id;
            $member->update();
            error_log("Updated member $member_id membership status to $membershiptype_id");
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
