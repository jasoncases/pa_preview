<?php

namespace Proaction\System\Resource\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as Logger;
use Proaction\Domain\Clients\Resource\ProactionClient;

class LogLevel
{

    protected $logger;
    protected $logSuffix = 'system';
    protected $level = 'debug';

    public function __construct($bypass = false)
    {
        if (!$bypass) {
            $this->_init();
        }
    }

    protected function _init()
    {
        $this->_registerLogger();
    }

    protected function _registerLogger()
    {
        $this->logger = new Logger('proaction_logs');
        $this->logger->pushHandler(new StreamHandler($this->_generateFilename(), Logger::DEBUG));
    }

    protected function _generateFilename()
    {
        $this->_generateConnection();
        return './logs/' . date('Ymd') . '-' . ProactionClient::prefix() . '-' . $this->logSuffix . '.log'; //. rand(0, 1000);
    }

    public function log(string $string, $data = [])
    {
        $this->_additionalActions($string, $data);
        return $this->_sendLog($string, $data);
    }

    protected function _additionalActions(string $string, $data = [])
    {
    }

    protected function _sendLog(string $string, $data = [])
    {
        try {
            $level = strtolower($this->level);
            $this->logger->{$level}($string, $data);
        } catch (\Exception $e) {

            $em = date("Y-m-d H:i:s") . $e->getMessage() . print_r(debug_backtrace(), true);
            mail("jeff@jasoncases.com", "ERROR WITH LOGGING", $em);
        }
    }

    protected function _generateConnection()
    {
    }

    public function bypasslog($message, $context = [])
    {
        $filename = './logs/nonexistant_route_log.log';
        $this->logger = new Logger('proaction_logs');
        $this->logger->pushHandler(new StreamHandler($filename, Logger::DEBUG));
        $this->_additionalActions($message, $context);
        return $this->_sendLog($message, $context);
    }
}
