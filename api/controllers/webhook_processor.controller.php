<?php

namespace Controllers;

/**
 * Controller for webhook queue processing operations
 * Allows remote authenticated execution of webhook processor commands
 */
class WebhookProcessorCtl {

    /**
     * Process pending webhooks from the queue
     *
     * POST body can include:
     * - batch_size: Number of events to process (default: 10, max: 100)
     * - iterations: Number of iterations to run (default: 1)
     * - sleep_seconds: Seconds to sleep between iterations (default: 0 for remote calls)
     *
     * Example:
     * POST /webhook/process
     * {
     *   "batch_size": 20,
     *   "iterations": 5,
     *   "sleep_seconds": 2
     * }
     */
    public static function process() {
        try {
            // Get JSON body
            $data = json_decode(file_get_contents("php://input"), true);

            // Parse options with defaults suitable for remote calls
            $batch_size = isset($data['batch_size']) ? (int)$data['batch_size'] : 10;
            $iterations = isset($data['iterations']) ? (int)$data['iterations'] : 1;
            $sleep_seconds = isset($data['sleep_seconds']) ? (int)$data['sleep_seconds'] : 0;

            // Validate options
            if ($batch_size < 1 || $batch_size > 100) {
                http_response_code(400);
                echo json_encode(array(
                    "error" => "batch_size must be between 1 and 100"
                ));
                return;
            }

            if ($iterations < 1 || $iterations > 50) {
                http_response_code(400);
                echo json_encode(array(
                    "error" => "iterations must be between 1 and 50"
                ));
                return;
            }

            if ($sleep_seconds < 0 || $sleep_seconds > 60) {
                http_response_code(400);
                echo json_encode(array(
                    "error" => "sleep_seconds must be between 0 and 60"
                ));
                return;
            }

            // Capture output
            ob_start();

            $processor = new \Workers\WebhookQueueProcessor();
            $processor->run($batch_size, $sleep_seconds, $iterations);

            $output = ob_get_clean();

            // Parse the output to extract information
            $processed = 0;
            $failed = 0;
            if (preg_match_all('/Processed event (\d+)/', $output, $matches)) {
                $processed = count($matches[1]);
            }
            if (preg_match_all('/Failed to process event/', $output, $matches)) {
                $failed = count($matches[0]);
            }

            http_response_code(200);
            echo json_encode(array(
                "message" => "Processing completed",
                "batch_size" => $batch_size,
                "iterations" => $iterations,
                "processed_events" => $processed,
                "failed_events" => $failed,
                "output" => $output
            ));

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(array(
                "error" => "Processing failed",
                "message" => $e->getMessage()
            ));
        }
    }

    /**
     * Get queue statistics
     *
     * GET /webhook/stats
     */
    public static function stats() {
        try {
            $webhook_queue = new \Models\WebhookQueue();
            $stats = $webhook_queue->getStats();

            if ($stats) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Queue statistics",
                    "stats" => $stats
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "error" => "Failed to retrieve statistics"
                ));
            }

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(array(
                "error" => "Failed to get statistics",
                "message" => $e->getMessage()
            ));
        }
    }

    /**
     * Reset stuck events in the queue
     * Events that have been processing for more than the timeout period
     *
     * POST body can include:
     * - timeout_minutes: Minutes before considering an event stuck (default: 30)
     *
     * POST /webhook/reset-stuck
     * {
     *   "timeout_minutes": 30
     * }
     */
    public static function resetStuck() {
        try {
            // Get JSON body
            $data = json_decode(file_get_contents("php://input"), true);

            $timeout_minutes = isset($data['timeout_minutes']) ? (int)$data['timeout_minutes'] : 30;

            // Validate timeout
            if ($timeout_minutes < 1 || $timeout_minutes > 1440) {
                http_response_code(400);
                echo json_encode(array(
                    "error" => "timeout_minutes must be between 1 and 1440 (24 hours)"
                ));
                return;
            }

            $processor = new \Workers\WebhookQueueProcessor();
            $reset_count = $processor->resetStuckEvents($timeout_minutes);

            http_response_code(200);
            echo json_encode(array(
                "message" => "Stuck events reset",
                "reset_count" => $reset_count,
                "timeout_minutes" => $timeout_minutes
            ));

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(array(
                "error" => "Failed to reset stuck events",
                "message" => $e->getMessage()
            ));
        }
    }
}
