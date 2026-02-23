<?php

namespace PostContentSync;

/**
 * Logger Class
 *
 * Handles logging to plugin's debug.log file.
 */
class Logger
{
    /**
     * Log file path
     *
     * @var string
     */
    private static $logFile;

    /**
     * Plugin configuration
     *
     * @var array
     */
    private static $config;

    /**
     * Initialize logger
     *
     * @param array $config Plugin configuration
     * @return void
     */
    public static function init($config)
    {
        self::$config = $config;
        self::$logFile = dirname(__DIR__) . '/debug.log';

        // Ensure log file exists and is writable
        if (!file_exists(self::$logFile)) {
            @touch(self::$logFile);
        }

        // Make sure directory is writable
        if (!is_writable(dirname(self::$logFile))) {
            error_log('Post Content Sync: Log directory is not writable: ' . dirname(self::$logFile));
        }
    }

    /**
     * Log a message
     *
     * @param string $level Log level (debug, info, warning, error)
     * @param string $message Log message
     * @param mixed $context Additional context data
     * @return void
     */
    public static function log($level, $message, $context = null)
    {
        // Check if logging is enabled
        $logLevel = self::$config['log_level'] ?? 'debug';
        $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

        if (!isset($levels[$level]) || !isset($levels[$logLevel])) {
            return;
        }

        // Only log if message level is >= configured level
        if ($levels[$level] < $levels[$logLevel]) {
            return;
        }

        // Format log entry
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        $logEntry = "[{$timestamp}] [{$levelUpper}] {$message}";

        // Add context if provided
        if ($context !== null) {
            $logEntry .= "\n" . var_export($context, true);
        }

        $logEntry .= "\n";

        // Write to log file using error_log
        error_log($logEntry, 3, self::$logFile);
    }

    /**
     * Log debug message
     *
     * @param string $message Log message
     * @param mixed $context Additional context data
     * @return void
     */
    public static function debug($message, $context = null)
    {
        self::log('debug', $message, $context);
    }

    /**
     * Log info message
     *
     * @param string $message Log message
     * @param mixed $context Additional context data
     * @return void
     */
    public static function info($message, $context = null)
    {
        self::log('info', $message, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message Log message
     * @param mixed $context Additional context data
     * @return void
     */
    public static function warning($message, $context = null)
    {
        self::log('warning', $message, $context);
    }

    /**
     * Log error message
     *
     * @param string $message Log message
     * @param mixed $context Additional context data
     * @return void
     */
    public static function error($message, $context = null)
    {
        self::log('error', $message, $context);
    }

    /**
     * Clear log file
     *
     * @return void
     */
    public static function clear()
    {
        if (file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
        }
    }

    /**
     * Get log file path
     *
     * @return string
     */
    public static function getLogFile()
    {
        return self::$logFile;
    }
}
