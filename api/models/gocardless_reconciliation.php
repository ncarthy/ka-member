<?php
namespace Models;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

/**
 * Builds a reconciliation summary between GoCardless events and local DB changes.
 */
class GoCardlessReconciliation {
    private $conn;
    private $client;
    private $mandate_table_name = null;
    private $mandate_has_subscription_column = null;

    public function __construct() {
        $this->conn = \Core\Database::getInstance()->conn;

        $access_token = getenv(\Core\Config::read('gocardless.access_token'));
        $environment = \Core\Config::read('gocardless.environment') ?? 'sandbox';

        $this->client = new \GoCardlessPro\Client([
            'access_token' => $access_token,
            'environment'  => $environment === 'live'
                ? \GoCardlessPro\Environment::LIVE
                : \GoCardlessPro\Environment::SANDBOX
        ]);
    }

    public function summarize($days = 7) {
        $days = (int)$days;
        if ($days < 1) {
            $days = 7;
        }

        $end = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $start = $end->sub(new DateInterval('P' . $days . 'D'));

        $events = [];
        $api_error = null;

        try {
            $events = $this->fetchEvents($start, $end);
        } catch (\Exception $e) {
            $api_error = $e->getMessage();
        }

        $summary_rows = $this->buildEventSummary($events);
        $comparison_rows = $this->buildComparisons($events);
        $warnings = [];

        $result = [
            'period' => [
                'days' => $days,
                'start_utc' => $start->format('Y-m-d\TH:i:s\Z'),
                'end_utc' => $end->format('Y-m-d\TH:i:s\Z')
            ],
            'gocardless' => [
                'event_count' => count($events),
                'summary' => $summary_rows
            ],
            'database' => [
                'changes' => $this->databaseChanges($start, $end),
                'webhook_log' => $this->webhookLogSummary($start, $end)
            ],
            'comparison' => $comparison_rows
        ];

        if (count($events) > 0 && !$this->hasComparableEventTypes($events)) {
            $warnings[] = 'GoCardless returned events, but none of the comparable types were present (mandates.created, subscriptions.created, subscriptions.cancelled|failed|expired, payments.created|confirmed).';
        }

        if ($api_error !== null) {
            $warnings[] = 'GoCardless API call failed. Returned DB-only information.';
            $result['api_error'] = $api_error;
        }

        if (!empty($warnings)) {
            $result['warnings'] = $warnings;
        }

        return $result;
    }

    private function fetchEvents($start, $end) {
        $params = [
            'created_at[gte]' => $start->format('Y-m-d\TH:i:s\Z'),
            'created_at[lte]' => $end->format('Y-m-d\TH:i:s\Z'),
            'limit' => 500
        ];

        $response = $this->client->events()->list(['params' => $params]);
        $records = $response->records ?? [];

        $events = [];
        foreach ($records as $event) {
            $resource_type = $event->resource_type ?? '';
            $action = $event->action ?? '';

            $events[] = [
                'event_id' => $event->id ?? '',
                'resource_type' => $resource_type,
                'action' => $action,
                'event_type' => $resource_type . '.' . $action,
                'resource_id' => $this->extractResourceId($event, $resource_type),
                'created_at' => $this->normalizeDate($event->created_at ?? null)
            ];
        }

        return $events;
    }

    private function normalizeDate($value) {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
        }

        return (string)$value;
    }

    private function extractResourceId($event, $resource_type) {
        if (!isset($event->links)) {
            return null;
        }

        $links = $event->links;

        if (is_object($links) && isset($links->{$resource_type})) {
            return (string)$links->{$resource_type};
        }

        $singular = rtrim((string)$resource_type, 's');
        if (is_object($links) && isset($links->{$singular})) {
            return (string)$links->{$singular};
        }

        if (is_object($links)) {
            foreach (get_object_vars($links) as $value) {
                if (is_scalar($value) && $value !== '') {
                    return (string)$value;
                }
            }
        }

        return null;
    }

    private function buildEventSummary($events) {
        $summary = [];
        foreach ($events as $event) {
            $type = $event['event_type'];
            if (!isset($summary[$type])) {
                $summary[$type] = 0;
            }
            $summary[$type]++;
        }

        ksort($summary);

        $rows = [];
        foreach ($summary as $event_type => $count) {
            $rows[] = [
                'event_type' => $event_type,
                'count' => $count
            ];
        }

        return $rows;
    }

    private function buildComparisons($events) {
        $by_type = [];

        foreach ($events as $event) {
            $event_type = $event['event_type'];
            if (!isset($by_type[$event_type])) {
                $by_type[$event_type] = [];
            }

            if (!empty($event['resource_id'])) {
                $by_type[$event_type][$event['resource_id']] = true;
            }
        }

        $rows = [];

        $rows[] = $this->compareMandatesCreated(array_keys($by_type['mandates.created'] ?? []));
        $payment_ids = array_keys(
            ($by_type['payments.created'] ?? []) +
            ($by_type['payments.confirmed'] ?? [])
        );
        $rows[] = $this->comparePaymentsCreated($payment_ids);
        $rows[] = $this->compareSubscriptionsCreated(array_keys($by_type['subscriptions.created'] ?? []));

        $terminated_ids = array_keys(
            ($by_type['subscriptions.cancelled'] ?? []) +
            ($by_type['subscriptions.failed'] ?? []) +
            ($by_type['subscriptions.expired'] ?? [])
        );
        $rows[] = $this->compareSubscriptionsTerminated($terminated_ids);

        return $rows;
    }

    private function hasComparableEventTypes($events) {
        foreach ($events as $event) {
            $event_type = $event['event_type'] ?? '';
            if (
                $event_type === 'mandates.created' ||
                $event_type === 'subscriptions.created' ||
                $event_type === 'subscriptions.cancelled' ||
                $event_type === 'subscriptions.failed' ||
                $event_type === 'subscriptions.expired' ||
                $event_type === 'payments.created' ||
                $event_type === 'payments.confirmed'
            ) {
                return true;
            }
        }

        return false;
    }

    private function compareMandatesCreated($mandate_ids) {
        $mandate_table = $this->mandateTableName();
        $found = $this->findExistingValues(
            "SELECT gc_mandate_id AS value FROM " . $mandate_table . " WHERE gc_mandate_id IN (%s)",
            $mandate_ids
        );
        $missing = array_values(array_diff($mandate_ids, $found));

        return [
            'event_type' => 'mandates.created',
            'event_count' => count($mandate_ids),
            'matched_count' => count($found),
            'missing_count' => count($missing),
            'missing_ids' => array_slice($missing, 0, 50)
        ];
    }

    private function comparePaymentsCreated($payment_ids) {
        if (empty($payment_ids)) {
            return [
                'event_type' => 'payments.(created|confirmed)',
                'event_count' => 0,
                'matched_count' => 0,
                'missing_count' => 0,
                'missing_ids' => [],
                'missing_details' => []
            ];
        }

        $notes = [];
        $lookup = [];
        foreach ($payment_ids as $payment_id) {
            $note = 'GoCardless payment ' . $payment_id;
            $notes[] = $note;
            $lookup[$note] = $payment_id;
        }

        $placeholders = implode(',', array_fill(0, count($notes), '?'));
        $query = "SELECT note FROM `transaction` WHERE note IN ($placeholders)";

        $stmt = $this->conn->prepare($query);
        foreach ($notes as $index => $note) {
            $stmt->bindValue($index + 1, $note, PDO::PARAM_STR);
        }
        $stmt->execute();

        $found = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $note = $row['note'] ?? '';
            if (isset($lookup[$note])) {
                $found[] = $lookup[$note];
            }
        }

        $found = array_values(array_unique($found));
        $missing = array_values(array_diff($payment_ids, $found));
        $missing_details = $this->buildMissingPaymentDetails($missing);

        return [
            'event_type' => 'payments.(created|confirmed)',
            'event_count' => count($payment_ids),
            'matched_count' => count($found),
            'missing_count' => count($missing),
            'missing_ids' => array_slice($missing, 0, 50),
            'missing_details' => $missing_details
        ];
    }

    private function buildMissingPaymentDetails($payment_ids) {
        $details = [];
        $payment_ids = array_slice(array_values(array_unique($payment_ids)), 0, 50);

        foreach ($payment_ids as $payment_id) {
            $details[] = $this->fetchMissingPaymentDetail($payment_id);
        }

        return $details;
    }

    private function fetchMissingPaymentDetail($payment_id) {
        $detail = [
            'payment_id' => (string)$payment_id,
            'amount' => null,
            'amount_display' => null,
            'charge_date' => null,
            'currency' => null,
            'mandate_id' => null,
            'mandate_type' => null,
            'mandate_scheme' => null,
            'customer_name' => null,
            'customer_id' => null,
            'error' => null
        ];

        try {
            $payment = $this->client->payments()->get($payment_id);
            $amount_minor = isset($payment->amount) ? (int)$payment->amount : null;
            $currency = isset($payment->currency) ? strtoupper((string)$payment->currency) : 'GBP';

            $detail['amount'] = $amount_minor;
            $detail['currency'] = $currency;
            $detail['amount_display'] = $this->formatMinorAmount($amount_minor, $currency);
            $detail['charge_date'] = $payment->charge_date ?? null;

            $mandate_id = null;
            if (isset($payment->links) && is_object($payment->links) && isset($payment->links->mandate)) {
                $mandate_id = (string)$payment->links->mandate;
            }
            $detail['mandate_id'] = $mandate_id;

            if (!empty($mandate_id)) {
                $mandate = $this->client->mandates()->get($mandate_id);
                $detail['mandate_scheme'] = isset($mandate->scheme) ? (string)$mandate->scheme : null;

                $customer_id = null;
                if (isset($mandate->links) && is_object($mandate->links) && isset($mandate->links->customer)) {
                    $customer_id = (string)$mandate->links->customer;
                }
                $detail['customer_id'] = $customer_id;

                if (!empty($customer_id)) {
                    $customer = $this->client->customers()->get($customer_id);
                    $detail['customer_name'] = $this->buildCustomerName($customer);
                }

                $subscription_id = $this->findSubscriptionIdByMandateId($mandate_id);
                if (!empty($subscription_id)) {
                    try {
                        $subscription = $this->client->subscriptions()->get($subscription_id);
                        $detail['mandate_type'] = isset($subscription->name) ? (string)$subscription->name : null;
                    } catch (\Exception $e) {
                        // Keep the rest of the detail if subscription lookup fails.
                    }
                }
            }
        } catch (\Exception $e) {
            $detail['error'] = $e->getMessage();
        }

        return $detail;
    }

    private function buildCustomerName($customer) {
        $given = isset($customer->given_name) ? trim((string)$customer->given_name) : '';
        $family = isset($customer->family_name) ? trim((string)$customer->family_name) : '';
        $company = isset($customer->company_name) ? trim((string)$customer->company_name) : '';

        $full_name = trim($given . ' ' . $family);
        if ($full_name !== '') {
            return $full_name;
        }

        if ($company !== '') {
            return $company;
        }

        return null;
    }

    private function formatMinorAmount($amount_minor, $currency) {
        if ($amount_minor === null) {
            return null;
        }

        return sprintf('%s %.2f', $currency, ((float)$amount_minor) / 100);
    }

    private function findSubscriptionIdByMandateId($mandate_id) {
        if (!$this->mandateTableHasSubscriptionColumn()) {
            return null;
        }

        $query = "SELECT gc_subscriptionid
                  FROM " . $this->mandateTableName() . "
                  WHERE gc_mandate_id = :mandate_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mandate_id', $mandate_id, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $subscription_id = trim((string)($row['gc_subscriptionid'] ?? ''));
        return $subscription_id === '' ? null : $subscription_id;
    }

    private function mandateTableHasSubscriptionColumn() {
        if ($this->mandate_has_subscription_column !== null) {
            return $this->mandate_has_subscription_column;
        }

        $query = "SELECT COUNT(*) AS column_count
                  FROM information_schema.columns
                  WHERE table_schema = DATABASE()
                    AND table_name = :table_name
                    AND column_name = 'gc_subscriptionid'";

        $table_name = $this->mandateTableName();
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':table_name', $table_name, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->mandate_has_subscription_column = $row && (int)$row['column_count'] > 0;
        return $this->mandate_has_subscription_column;
    }

    private function compareSubscriptionsCreated($subscription_ids) {
        $mandate_table = $this->mandateTableName();
        $found = $this->findExistingValues(
            "SELECT gc_subscriptionid AS value FROM " . $mandate_table . " WHERE gc_subscriptionid IN (%s)",
            $subscription_ids
        );
        $missing = array_values(array_diff($subscription_ids, $found));

        return [
            'event_type' => 'subscriptions.created',
            'event_count' => count($subscription_ids),
            'matched_count' => count($found),
            'missing_count' => count($missing),
            'missing_ids' => array_slice($missing, 0, 50)
        ];
    }

    private function compareSubscriptionsTerminated($subscription_ids) {
        if (empty($subscription_ids)) {
            return [
                'event_type' => 'subscriptions.(cancelled|failed|expired)',
                'event_count' => 0,
                'matched_count' => 0,
                'missing_count' => 0,
                'missing_ids' => []
            ];
        }

        $placeholders = implode(',', array_fill(0, count($subscription_ids), '?'));
        $mandate_table = $this->mandateTableName();
        $query = "SELECT gm.gc_subscriptionid AS subscription_id, m.membership_idmembership AS status_id
                  FROM " . $mandate_table . " gm
                  INNER JOIN member m ON m.idmember = gm.member_idmember
                  WHERE gm.gc_subscriptionid IN ($placeholders)";

        $stmt = $this->conn->prepare($query);
        foreach ($subscription_ids as $index => $subscription_id) {
            $stmt->bindValue($index + 1, $subscription_id, PDO::PARAM_STR);
        }
        $stmt->execute();

        $former = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ((int)$row['status_id'] === 9) {
                $former[] = $row['subscription_id'];
            }
        }

        $former = array_values(array_unique($former));
        $missing = array_values(array_diff($subscription_ids, $former));

        return [
            'event_type' => 'subscriptions.(cancelled|failed|expired)',
            'event_count' => count($subscription_ids),
            'matched_count' => count($former),
            'missing_count' => count($missing),
            'missing_ids' => array_slice($missing, 0, 50)
        ];
    }

    private function findExistingValues($query_template, $values) {
        if (empty($values)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $query = sprintf($query_template, $placeholders);

        $stmt = $this->conn->prepare($query);
        foreach ($values as $index => $value) {
            $stmt->bindValue($index + 1, $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        $found = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $found[] = (string)$row['value'];
        }

        return array_values(array_unique($found));
    }

    private function databaseChanges($start, $end) {
        $start_date = $start->format('Y-m-d');
        $end_date = $end->format('Y-m-d');
        $start_dt = $start->format('Y-m-d H:i:s');
        $end_dt = $end->format('Y-m-d H:i:s');

        $members_created = 0;
        $mandates_created = 0;
        $transactions_created = 0;

        $query = "SELECT COUNT(*) AS count
                  FROM member
                  WHERE username = 'gocardless_webhook'
                    AND joindate >= :start_date
                    AND joindate <= :end_date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $members_created = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $query = "SELECT COUNT(*) AS count
                  FROM " . $this->mandateTableName() . "
                  WHERE created_at >= :start_dt
                    AND created_at <= :end_dt";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_dt', $start_dt);
        $stmt->bindParam(':end_dt', $end_dt);
        $stmt->execute();
        $mandates_created = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $query = "SELECT COUNT(*) AS count
                  FROM `transaction`
                  WHERE note LIKE 'GoCardless payment %'
                    AND `date` >= :start_date
                    AND `date` <= :end_date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $transactions_created = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'members_created' => $members_created,
            'mandates_created' => $mandates_created,
            'transactions_created' => $transactions_created
        ];
    }

    private function webhookLogSummary($start, $end) {
        $start_dt = $start->format('Y-m-d H:i:s');
        $end_dt = $end->format('Y-m-d H:i:s');

        $query = "SELECT resource_type, action, processed, COUNT(*) AS count
                  FROM webhook_log
                  WHERE created_at >= :start_dt
                    AND created_at <= :end_dt
                  GROUP BY resource_type, action, processed
                  ORDER BY resource_type, action, processed";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_dt', $start_dt);
        $stmt->bindParam(':end_dt', $end_dt);
        $stmt->execute();

        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                'event_type' => $row['resource_type'] . '.' . $row['action'],
                'processed' => (int)$row['processed'],
                'count' => (int)$row['count']
            ];
        }

        return $rows;
    }

    private function mandateTableName() {
        if ($this->mandate_table_name !== null) {
            return $this->mandate_table_name;
        }

        $candidate_tables = ['gocardless_mandate', 'gocardless'];

        foreach ($candidate_tables as $table_name) {
            $query = "SELECT COUNT(*) AS table_count
                      FROM information_schema.tables
                      WHERE table_schema = DATABASE()
                        AND table_name = :table_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':table_name', $table_name);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && (int)$row['table_count'] > 0) {
                $this->mandate_table_name = $table_name;
                return $table_name;
            }
        }

        // Keep default so the API error clearly states missing table.
        $this->mandate_table_name = 'gocardless_mandate';
        return $this->mandate_table_name;
    }
}
