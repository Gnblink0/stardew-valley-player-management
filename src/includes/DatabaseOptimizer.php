<?php

class DatabaseOptimizer {
    private static $instance = null;
    private $pdo;
    private $queryCache = [];
    private $cacheEnabled = true;
    private $cacheTimeout = 300; // 5 minutes
    private $performanceMonitor;

    private function __construct($pdo) {
        $this->pdo = $pdo;
        $this->performanceMonitor = PerformanceMonitor::getInstance();
    }

    public static function getInstance($pdo) {
        if (self::$instance === null) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function setCacheEnabled($enabled) {
        $this->cacheEnabled = $enabled;
    }

    public function setCacheTimeout($timeout) {
        $this->cacheTimeout = $timeout;
    }

    public function query($sql, $params = [], $useCache = true) {
        $cacheKey = $this->generateCacheKey($sql, $params);
        
        if ($useCache && $this->cacheEnabled) {
            $cachedResult = $this->getFromCache($cacheKey);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $this->performanceMonitor->startQuery($sql);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->performanceMonitor->endQuery($sql);
            
            if ($useCache && $this->cacheEnabled) {
                $this->addToCache($cacheKey, $result);
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->performanceMonitor->endQuery($sql);
            throw $e;
        }
    }

    public function execute($sql, $params = []) {
        $this->performanceMonitor->startQuery($sql);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            $this->performanceMonitor->endQuery($sql);
            
            return $result;
        } catch (PDOException $e) {
            $this->performanceMonitor->endQuery($sql);
            throw $e;
        }
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    private function generateCacheKey($sql, $params) {
        return md5($sql . serialize($params));
    }

    private function getFromCache($key) {
        if (isset($this->queryCache[$key])) {
            $cache = $this->queryCache[$key];
            if (time() - $cache['time'] < $this->cacheTimeout) {
                return $cache['data'];
            }
        }
        return false;
    }

    private function addToCache($key, $data) {
        $this->queryCache[$key] = [
            'time' => time(),
            'data' => $data
        ];
    }

    public function clearCache() {
        $this->queryCache = [];
    }

    public function optimizeTable($tableName) {
        $sql = "OPTIMIZE TABLE " . $this->pdo->quote($tableName);
        return $this->query($sql, [], false);
    }

    public function analyzeTable($tableName) {
        $sql = "ANALYZE TABLE " . $this->pdo->quote($tableName);
        return $this->query($sql, [], false);
    }

    public function getTableStatus($tableName) {
        $sql = "SHOW TABLE STATUS LIKE " . $this->pdo->quote($tableName);
        return $this->query($sql, [], false);
    }

    public function getIndexes($tableName) {
        $sql = "SHOW INDEX FROM " . $this->pdo->quote($tableName);
        return $this->query($sql, [], false);
    }

    public function getSlowQueries($limit = 10) {
        $sql = "SHOW FULL PROCESSLIST";
        $processes = $this->query($sql, [], false);
        
        $slowQueries = [];
        foreach ($processes as $process) {
            if ($process['Time'] > 1) { // Queries taking more than 1 second
                $slowQueries[] = $process;
            }
        }
        
        usort($slowQueries, function($a, $b) {
            return $b['Time'] - $a['Time'];
        });
        
        return array_slice($slowQueries, 0, $limit);
    }

    public function getConnectionStatus() {
        return [
            'connected' => $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'server_version' => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'client_version' => $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'connection_status' => $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'server_info' => $this->pdo->getAttribute(PDO::ATTR_SERVER_INFO)
        ];
    }

    public function getQueryStats() {
        return $this->performanceMonitor->getPerformanceData();
    }
} 