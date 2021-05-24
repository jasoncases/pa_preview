<?php

namespace Proaction\Domain\Payroll\Model;

use Proaction\System\Model\ClientModel;

/**
 *
 */
class Lunch extends ClientModel
{
    protected $table = 'v_payroll_todays_lunch';
    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_payroll_todays_lunch AS
                        SELECT
                        a.*
                        FROM ts_timesheet AS a
                        WHERE a.activity_id IN (5, -5)
                        AND DATE(a.time_stamp)=DATE(NOW());';
}
