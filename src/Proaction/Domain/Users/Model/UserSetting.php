<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;

class UserSetting extends ClientModel
{
    protected $table = 'user_settings';

    public static function batch($employee_id, $batchSettingsArr)
    {
        return (new static)->_batch($employee_id, $batchSettingsArr);
    }

    private function _batch($employee_id, $batchSettingsArr)
    {
        foreach ($batchSettingsArr as $k => $value) {
            $setting = SettingDefinitions::where('user_setting', $k)->first('id');
            if (!is_null($setting)) {
                UserSetting::p_create(
                    [
                        'employee_id' => $employee_id,
                        'setting_id' => $setting->id,
                        'value' => $value,
                    ]
                );
            }
        }
        return true;
    }

    public static function get($key, $employee_id)
    {
        $id = SettingDefinitions::where('user_setting', $key)->get('id');
        return self::where('setting_id', $id)
            ->where('employee_id', $employee_id)
            ->get('value');
    }

    /**
     * Update OR create a record for key value employee id trio
     *
     * @param string $key
     * @param mixed $value
     * @param int $employee_id
     * @return void
     */
    public static function updateValue($key, $value, $employee_id)
    {
        $setting_id = SettingDefinitions::where('user_setting', $key)->get('id');
        $id = self::where('setting_id', $setting_id)->where('employee_id', $employee_id)->get('id');
        if ($id) {
            return self::p_update(compact('id', 'value'));
        } else {
            return self::p_create(compact('setting_id', 'value', 'employee_id'));
        }
    }
}
