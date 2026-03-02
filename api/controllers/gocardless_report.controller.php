<?php

namespace Controllers;

class GoCardlessReportCtl {

    /**
     * GET /gocardless/reconciliation
     * Query params:
     * - period: week|month (default week)
     * - days: positive integer (overrides period)
     * - startDate,endDate: YYYY-MM-DD (overrides period/days)
     */
    public static function reconciliation() {
        $start_date_param = isset($_GET['startDate']) ? trim((string)$_GET['startDate']) : '';
        $end_date_param = isset($_GET['endDate']) ? trim((string)$_GET['endDate']) : '';
        $period_param = $_GET['period'] ?? ($_GET['p'] ?? 'week');
        $period = strtolower(trim($period_param));
        $days = null;
        $start_utc = null;
        $end_utc = null;
        $label = null;

        if ($start_date_param !== '' || $end_date_param !== '') {
            if ($start_date_param === '' || $end_date_param === '') {
                http_response_code(400);
                echo json_encode([
                    'message' => 'startDate and endDate must both be provided'
                ]);
                return;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date_param) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date_param)) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'startDate and endDate must be in YYYY-MM-DD format'
                ]);
                return;
            }

            try {
                $tz = new \DateTimeZone('UTC');
                $start_utc = new \DateTimeImmutable($start_date_param . ' 00:00:00', $tz);
                $end_utc = new \DateTimeImmutable($end_date_param . ' 23:59:59', $tz);
            } catch (\Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'Invalid startDate/endDate values'
                ]);
                return;
            }

            if ($end_utc < $start_utc) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'endDate must be on or after startDate'
                ]);
                return;
            }

            $label = ($start_date_param === '2000-01-01')
                ? 'any'
                : ($start_date_param . ' to ' . $end_date_param);
        }

        if ($start_utc === null && isset($_GET['days'])) {
            $days = (int)$_GET['days'];
            if ($days < 1 || $days > 365) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'days must be between 1 and 365'
                ]);
                return;
            }
        } else if ($start_utc === null) {
            if ($period === 'month') {
                $days = 30;
            } else {
                $days = 7;
            }
        }

        try {
            $model = new \Models\GoCardlessReconciliation();
            if ($start_utc !== null) {
                $report = $model->summarize(0, $start_utc, $end_utc);
                $report['period']['label'] = $label ?? 'custom';
            } else {
                $report = $model->summarize($days);
                $report['period']['label'] = $days === 30 ? 'month' : ($days === 7 ? 'week' : ($days . ' days'));
            }

            echo json_encode($report, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'message' => 'Failed to build reconciliation report',
                'error' => $e->getMessage()
            ]);
        }
    }
}
