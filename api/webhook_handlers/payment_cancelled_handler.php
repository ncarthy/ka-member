<?php
namespace WebhookHandlers;

use \PDO;

class PaymentCreatedHandler extends AbstractWebhookHandler {

    const BANK_ID = 5; // Lloyds bank account (receives GoCardless payments)
    const PAYMENT_TYPE_ID = 6; // Direct Debit payment type

    /**
     * Handle payments.confirmed event - creates transaction record
     * @param object $event Event object from GoCardless library
     * @param \Models\WebhookLog $webhook_log
     * @return array
     */
    public function handle($event, $webhook_log) {
        $payment_id = $event->links->payment ?? null;

        if (empty($payment_id)) {
            throw new \Exception('Missing required payment id');
        }

        // Delete payment from transaction table
        $delete_query = "DELETE FROM transaction
                         WHERE 	note  = :note
                         ";

        $stmt = $this->conn->prepare($delete_query);

        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new \Exception("Failed to prepare delete statement: " . $errorInfo[2]);
        }

        $note_clean = htmlspecialchars(strip_tags("GoCardless payment $payment_id"));
        $stmt->bindParam(":note", $note_clean);

        if (!$stmt->execute()) {


            return [
                'event_id' => $event->id,
                'status' => 'success',
                'payment_id' => $payment_id,
            ];
        } else {
            // Get detailed error information
            $errorInfo = $stmt->errorInfo();
            $errorMsg = "Failed to delete transaction record - " . $errorInfo[2];
            error_log($errorMsg);
            throw new \Exception($errorMsg);
        }
    }
}
