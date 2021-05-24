<?php

namespace Proaction\System\Resource\Subscribers;

use Proaction\System\Database\CDB;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

class ComponentQuery {

    protected $srcMap = [
        'employee' => '\Proaction\Domain\Employees\Model\EmployeeView',
        'taskSubscriber' => '\Proaction\Domain\Tasks\Model\TaskUsersView',
        'ticketSubscriber' => '\Proaction\Domain\Tickets\Model\TicketUsersView',
        'codeSubscriber' => '\Proaction\Domain\Codes\Model\CodeUsersView',
        ];

    protected $type, $fields, $value, $target;

    public function __construct($options)
    {
        $this->type = $options['queryType'];
        $this->fields = $this->_validateIncomingJson($options['fields']);
        $tmpTar = $options['targetField'];
        $this->primaryTarget = is_array($tmpTar) ? current($tmpTar) : $tmpTar;
        $this->target = $this->_validateQueryTarget($options['targetField']);
        $this->query = $options['query'];
        $this->mods = $this->_validateIncomingJson($options['modifier']);
    }

    /**
     * queryType will point to a specific model
     * query will be the value
     * fields
     * targetField
     *
     * @param array $options
     */
    public static function query($options) {
        return (new ComponentQuery($options))->go();
    }

    public function go() {
        $val = $this->query;
        $model = $this->srcMap[$this->type]::where($this->target, "LIKE", "%$val%");
        foreach ($this->mods as $mod) {
            $op = $mod['operator'] ?? '=';
            $model->where($mod['key'], $op, $mod['value']);
        }
        return $model->oldest($this->primaryTarget)
            ->get(
               $this->_escapeFieldsForRawDB($this->fields)
            );
    }

    private function _validateQueryTarget($target) {
        if (is_array($target)) {
            $s = [];
            $s[] = 'LOWER( CONCAT( ';
            $inner = [];
            foreach ($target as $t) {
                $inner[] = "COALESCE($t, '')";
            }
            $s[] = implode(' , ', $inner);
            $s[] = ') )';
            $target = implode(' ', $s);
        }
        return $target;
    }

    private function _validateIncomingJson($prop) {
        if (Misc::isJson($prop)) {
            return json_decode($prop, true);
        }
        return $prop ?? [];
    }

    private function _escapeFieldsForRawDB($fields) {
        if (is_array($fields)) {
            foreach ($fields as $k => $v) {
                if (preg_match('/ as /', $v)) {
                    $fields[$k] = CDB::raw($v);
                }
            }
        }

        return $fields;
    }
}
