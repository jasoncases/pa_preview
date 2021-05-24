<?php

namespace Proaction\System\TicketBasedModules;

use Exception\IllegalValueException;
use Proaction\System\Resource\Status\Status;

abstract class Updater {

    /**
     * srcMap of supplied `type` keys to methods that will validate
     * option keys and call the proper state updating class
     *
     * @var array
     */
    protected $srcMap = [
        'alterStatus' => 'updateRecordStatus',
        'alterPriority' => 'updateRecordPriority',
        'updateCategory' => 'updateRecordCategory',
        'updateDescription' => 'updateRecordDescription',
        'updateTitle' => 'updateRecordTitle',
        'updateReply' => 'updateReply',
        'updateSubscribers' => 'updateSubscribers',
        'updateAssignees' => 'updateAssignees',
    ];
    
    protected $localSrcMap = [];

    public function __construct()
    {
        $this->srcMap = array_merge($this->srcMap, $this->localSrcMap);
    }

    public static function go($type, $options = []) {
        return (new static)->_go($type, $options);
    }

    /**
     *
     * @param array $options
     * @return array - either returns a TaskInterface array, or an array
     *                 of TaskInterfaces depending on if the record that
     *                 is being updated is PerpetualTask or Task
     */
    abstract protected function updateRecordStatus($options); 

    abstract protected function updateRecordPriority($options);

    abstract protected function updateRecordCategory($options);

    abstract protected function updateRecordDescription($options);

    abstract protected function updateRecordTitle($options);

    abstract protected function updateNotifyManager($options);

    abstract protected function updateSubscribers($options); 
    
    abstract protected function updateReply($options); 
    /**
     * Throws an exception if the keys in the options array do not match
     * the supplied requiredKeys
     *
     * @param array $requiredKeys
     * @param array $options
     * @return void
     */
    protected function _validate($requiredKeys, $options) {
        if (array_diff($requiredKeys, array_keys($options))) {
            throw new IllegalValueException("Provided options missing at least one required key: " . print_r($options, true));
        }
    }

    /**
     * 
     * @param string $type
     * @param array $options
     * @return array - either returns a TaskInterface array, or an array
     *                 of TaskInterfaces depending on if the record that
     *                 is being updated is PerpetualTask or Task
     */
    protected function _go($type, $options) {
        // select the method based on the key in the srcMap and supplied
        // type
        try {
            return $this->{$this->srcMap[$this->_validateType($type)]}($options);
        } catch (\Exception $e) {
            return (new Status())->aux(['msg' => $e->getMessage()])->error();
        }
    }

    protected function _validateType($type){
        if(!array_key_exists($type, $this->srcMap)){
            throw new IllegalValueException("Provided type [$type] does not match source map keys");
        }
        return $type;
    }
}