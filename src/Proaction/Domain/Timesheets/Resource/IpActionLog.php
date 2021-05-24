<?php

namespace Proaction\Domain\Timesheets\Resource;

use Proaction\Domain\Clients\Model\IpWhiteList;
use Proaction\Domain\Timesheets\Model\TimesheetIpLog;
use Proaction\System\Resource\Comms\Comms;
use Proaction\System\Resource\Config\DotEnv;
use Proaction\System\Resource\Helpers\Arr;

class IpActionLog
{

    private $record;
    private $ipAllowedList;

    /**
     * punchRecord format:
     *
     * `id` [timesheet_id]
     * `time_stamp`
     * `employee_name` [fullDisplayName]
     * `punch_type` [text]
     * `_ip` $_SERVER['REMOTE_ADDR']
     *
     *
     * @param [type] $punchRecord
     */
    public function __construct($punchRecord)
    {
        $this->record = $punchRecord;
        $this->ipAllowedList = $this->_createAllowedList();
    }

    public function do()
    {
        $this->_logIp();
        $this->_sendComms();
        return true;
    }

    private function _logIp()
    {
        return TimesheetIpLog::p_create(
            [
                'timesheet_id' => $this->record->id,
                'uri' => $_SERVER['REQUEST_URI'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );
    }

    private function _sendComms()
    {

        try {

            if (!boolval($this->record->_ip)) {
                return;
            }

            if (!in_array($this->record->_ip, $this->ipAllowedList)) {
                return Comms::send(
                    'email',
                    'system.remoteTimeclock',
                    $this->record->toArray(),
                );
            }
        } catch (\Exception $e) {
            die('send comms: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve Client allowed IP list from their client_ip_whitelist
     * table. Merge it with the server IPs in the ~/.env file
     *
     * @return array
     */
    private function _createAllowedList()
    {
        $c = [];
        $c[] = DotEnv::get('SERVER_IP_ONE');
        $c[] = DotEnv::get('SERVER_IP_TWO');
        $whitelist = IpWhiteList::all();
        $c = array_merge($c, Arr::flatten($whitelist->pluck('ip_add')->toArray() ?? []));
        return $c;
    }
}
