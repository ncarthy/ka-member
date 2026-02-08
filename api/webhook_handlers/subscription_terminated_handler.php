<?php
namespace WebhookHandlers;

class SubscriptionTerminatedHandler extends AbstractWebhookHandler {

    /**
     * Handle subscription termination events (cancelled, failed, expired)
     * @param object $event Event object from GoCardless library
     * @param \Models\WebhookLog $webhook_log
     * @return array
     */
    public function handle($event, $webhook_log) {
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
