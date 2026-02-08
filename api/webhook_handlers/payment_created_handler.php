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
}
