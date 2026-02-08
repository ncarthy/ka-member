<?php
namespace WebhookHandlers;

use \PDO;
use \Models\Member;

class SubscriptionTerminatedHandler extends AbstractWebhookHandler {

    const FORMERMEMBER_MEMBERSHIP_STATUS_ID = 9;

    /**
     * Handle subscription termination events (cancelled, failed, expired)
     * @param object $event Event object from GoCardless library
     * @param \Models\WebhookLog $webhook_log
     * @return array
     */
    public function handle($event, $webhook_log) {

        $action = $event->action ?? '';
        $subscription_id = $event->links->subscription ?? null;

        if (empty($subscription_id)) {
            throw new \Exception('Missing subscription ID in event');
        }

        // Step 1: Fetch subscription details from GoCardless API
        $subscription = $this->getSubscriptionDetails($subscription_id);
        $mandate_id = $subscription->links->mandate ?? null;
        if (empty($mandate_id)) {
            throw new \Exception('Missing required mandate ID in subscription data');
        }

        // Find member by mandate ID (bankpayerref)
        $member_query = "SELECT member_idmember
                         FROM subscription
                         WHERE 	gc_mandate_id  = :mandate_id
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
        $member_id = $row['member_idmember'];
        if (empty($member_id)) {
            throw new \Exception("No member found with mandate ID: $mandate_id");
        }     

        // Find member record using idmember
        $member = new Member();
        $member->id = $member_id;
        try {
            $member->readOne();
        } catch (\Exception $e) {
            throw new \Exception("Failed to read member record for member ID: $member_id - " . $e->getMessage());
        }

        $member->statusID = self::FORMERMEMBER_MEMBERSHIP_STATUS_ID;
        $member->deletedate = date('Y-m-d H:i:s');

        if (!$member->update()) {
            throw new \Exception("Failed to update member record for member ID: $member_id");
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
