<?php

class ErrorHandler {
    private static $instance = null;
    private $errors = [];
    private $isDebugMode = false;
    private $logFile = '/tmp/stardew_valley_error.log';  // 默认日志文件路径

    private function __construct() {
        // 设置错误处理函数
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setDebugMode($mode) {
        $this->isDebugMode = $mode;
    }

    public function setLogFile($path) {
        $this->logFile = $path;
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        $error = [
            'type' => $this->getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'time' => date('Y-m-d H:i:s')
        ];

        $this->errors[] = $error;
        $this->logError($error);

        if ($this->isDebugMode) {
            $this->displayError($error);
        }

        return true;
    }

    public function handleException($exception) {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'time' => date('Y-m-d H:i:s')
        ];

        $this->errors[] = $error;
        $this->logError($error);

        if ($this->isDebugMode) {
            $this->displayError($error);
        } else {
            $this->displayUserFriendlyError();
        }
    }

    public function handleFatalError() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private function getErrorType($errno) {
        switch ($errno) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }

    private function logError($error) {
        if (!$this->logFile) {
            return;  // 如果没有设置日志文件，直接返回
        }

        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            $error['time'],
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        if (isset($error['trace'])) {
            $logMessage .= "Stack trace:\n" . $error['trace'] . "\n";
        }

        error_log($logMessage, 3, $this->logFile);
    }

    private function displayError($error) {
        if (headers_sent()) {
            echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red;'>";
            echo "<h3>Error Details:</h3>";
            echo "<p><strong>Type:</strong> {$error['type']}</p>";
            echo "<p><strong>Message:</strong> {$error['message']}</p>";
            echo "<p><strong>File:</strong> {$error['file']}</p>";
            echo "<p><strong>Line:</strong> {$error['line']}</p>";
            if (isset($error['trace'])) {
                echo "<p><strong>Stack Trace:</strong></p>";
                echo "<pre>" . htmlspecialchars($error['trace']) . "</pre>";
            }
            echo "</div>";
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $error['message'],
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
        }
    }

    private function displayUserFriendlyError() {
        if (headers_sent()) {
            echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red;'>";
            echo "<h3>Oops! Something went wrong.</h3>";
            echo "<p>We apologize for the inconvenience. Please try again later.</p>";
            echo "</div>";
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }

    public function getErrors() {
        return $this->errors;
    }

    public function clearErrors() {
        $this->errors = [];
    }
} 