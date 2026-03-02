<?php

namespace Controllers;

class GoCardlessReportCtl {

    /**
     * GET /gocardless/reconciliation
     * Query params:
     * - period: week|month (default week)
     * - days: positive integer (overrides period)
     */
    public static function reconciliation() {
        $period_param = $_GET['period'] ?? ($_GET['p'] ?? 'week');
        $period = strtolower(trim($period_param));
        $days = null;

        if (isset($_GET['days'])) {
            $days = (int)$_GET['days'];
            if ($days < 1 || $days > 365) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'days must be between 1 and 365'
                ]);
                return;
            }
        } else {
            if ($period === 'month') {
                $days = 30;
            } else {
                $days = 7;
            }
        }

        try {
            $model = new \Models\GoCardlessReconciliation();
            $report = $model->summarize($days);
            $report['period']['label'] = $days === 30 ? 'month' : ($days === 7 ? 'week' : ($days . ' days'));

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
