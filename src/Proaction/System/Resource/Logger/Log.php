<?php

namespace Proaction\System\Resource\Logger;

/**
 * This is a static facade wrap to the PSR-3 compliant monolog/monolog
 * package. This creates a LogLevel object based on the level
 */
class Log
{
    private $logger;

    protected function __construct()
    {
    }
    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug(string $message, $context = [])
    {
        return (new static)->_log($message, 'debug', $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info(string $message, $context = [])
    {
        return (new static)->_log($message, 'info', $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function notice(string $message, $context = [])
    {
        return (new static)->_log($message, 'notice', $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning(string $message, $context = [])
    {
        return (new static)->_log($message, 'warning', $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error(string $message, $context = [])
    {
        return (new static)->_log($message, 'error', $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function critical(string $message, $context = [])
    {
        return (new static)->_log($message, 'critical', $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function alert(string $message, $context = [])
    {
        return (new static)->_log($message, 'alert', $context);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function emergency(string $message, $context = [])
    {
        return (new static)->_log($message, 'emergency', $context);
    }

    public static function log($level, $message, $context)
    {
        return (new static)->_log($message, $context, $level);
    }

    private function _log(string $message, $level, $context = [])
    {
        return $this->_loggerLevel($level)->log($message, $context);
    }

    private function _loggerLevel($level)
    {
        $class = '\Proaction\System\Resource\Logger\Levels\\' . ucfirst(strtolower($level));
        return new $class();
    }
}
