<?php

class PerformanceMonitor {
    private static $instance = null;
    private $startTime;
    private $memoryStart;
    private $queries = [];
    private $timings = [];
    private $isEnabled = false;
    private $logFile = '/tmp/stardew_valley_performance.log';  // default log file path

    private function __construct() {
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enable() {
        $this->isEnabled = true;
    }

    public function disable() {
        $this->isEnabled = false;
    }

    public function setLogFile($path) {
        $this->logFile = $path;
    }

    public function startQuery($query) {
        if (!$this->isEnabled) return;
        
        $this->queries[] = [
            'query' => $query,
            'start_time' => microtime(true)
        ];
    }

    public function endQuery($query) {
        if (!$this->isEnabled) return;
        
        foreach ($this->queries as &$q) {
            if ($q['query'] === $query) {
                $q['end_time'] = microtime(true);
                $q['duration'] = $q['end_time'] - $q['start_time'];
                break;
            }
        }
    }

    public function startTiming($name) {
        if (!$this->isEnabled) return;
        
        $this->timings[$name] = [
            'start_time' => microtime(true)
        ];
    }

    public function endTiming($name) {
        if (!$this->isEnabled) return;
        
        if (isset($this->timings[$name])) {
            $this->timings[$name]['end_time'] = microtime(true);
            $this->timings[$name]['duration'] = $this->timings[$name]['end_time'] - $this->timings[$name]['start_time'];
        }
    }

    public function getPerformanceData() {
        if (!$this->isEnabled) return null;

        $endTime = microtime(true);
        $memoryEnd = memory_get_usage();
        $peakMemory = memory_get_peak_usage();

        $data = [
            'total_execution_time' => $endTime - $this->startTime,
            'memory_usage' => [
                'start' => $this->memoryStart,
                'end' => $memoryEnd,
                'peak' => $peakMemory,
                'current' => memory_get_usage(true)
            ],
            'queries' => $this->queries,
            'timings' => $this->timings
        ];

        // Calculate query statistics
        $data['query_stats'] = [
            'total_queries' => count($this->queries),
            'total_time' => 0,
            'average_time' => 0,
            'slowest_query' => null,
            'slowest_time' => 0
        ];

        foreach ($this->queries as $query) {
            if (isset($query['duration'])) {
                $data['query_stats']['total_time'] += $query['duration'];
                if ($query['duration'] > $data['query_stats']['slowest_time']) {
                    $data['query_stats']['slowest_time'] = $query['duration'];
                    $data['query_stats']['slowest_query'] = $query['query'];
                }
            }
        }

        if ($data['query_stats']['total_queries'] > 0) {
            $data['query_stats']['average_time'] = $data['query_stats']['total_time'] / $data['query_stats']['total_queries'];
        }

        return $data;
    }

    public function logPerformanceData() {
        if (!$this->isEnabled) return;

        $data = $this->getPerformanceData();
        if (!$data) return;

        if (!$this->logFile) {
            return;  // if no log file is set, return
        }

        $logMessage = sprintf(
            "[%s] Performance Report:\n" .
            "Total Execution Time: %.4f seconds\n" .
            "Memory Usage: %.2f MB\n" .
            "Peak Memory: %.2f MB\n" .
            "Total Queries: %d\n" .
            "Average Query Time: %.4f seconds\n" .
            "Slowest Query: %.4f seconds\n",
            date('Y-m-d H:i:s'),
            $data['total_execution_time'],
            $data['memory_usage']['current'] / 1024 / 1024,
            $data['memory_usage']['peak'] / 1024 / 1024,
            $data['query_stats']['total_queries'],
            $data['query_stats']['average_time'],
            $data['query_stats']['slowest_time']
        );

        if ($data['query_stats']['slowest_query']) {
            $logMessage .= "Slowest Query SQL: " . $data['query_stats']['slowest_query'] . "\n";
        }

        error_log($logMessage, 3, $this->logFile);
    }

    public function displayPerformanceData() {
        if (!$this->isEnabled) return;

        $data = $this->getPerformanceData();
        if (!$data) return;

        echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px; border: 1px solid #dee2e6;'>";
        echo "<h3>Performance Report</h3>";
        echo "<p><strong>Total Execution Time:</strong> " . number_format($data['total_execution_time'], 4) . " seconds</p>";
        echo "<p><strong>Memory Usage:</strong> " . number_format($data['memory_usage']['current'] / 1024 / 1024, 2) . " MB</p>";
        echo "<p><strong>Peak Memory:</strong> " . number_format($data['memory_usage']['peak'] / 1024 / 1024, 2) . " MB</p>";
        echo "<p><strong>Total Queries:</strong> " . $data['query_stats']['total_queries'] . "</p>";
        echo "<p><strong>Average Query Time:</strong> " . number_format($data['query_stats']['average_time'], 4) . " seconds</p>";
        echo "<p><strong>Slowest Query Time:</strong> " . number_format($data['query_stats']['slowest_time'], 4) . " seconds</p>";
        
        if ($data['query_stats']['slowest_query']) {
            echo "<p><strong>Slowest Query:</strong></p>";
            echo "<pre>" . htmlspecialchars($data['query_stats']['slowest_query']) . "</pre>";
        }
        
        echo "</div>";
    }
} 