]<?php

    namespace Proaction\System\Resource\Daemon\ShellDaemon;

    use Proaction\Domain\Schedules\Model\Schedule;
    use Proaction\Domain\Schedules\Model\ScheduleShift;
    use Proaction\Domain\Timesheets\Model\EmployeeTimeclockStatus;
    use Proaction\Domain\Timesheets\Model\Shift;
    use Proaction\System\Database\CDB;
    use Proaction\System\Resource\Helpers\Arr;

    /**
     * Collect employees in both clockedin and clocked out states. Compare
     * their times against globally set values to play voice/email alerts if
     * late, clocked in too long, etc
     *
     */
    class ShellSchedule
    {

        private $scheduledNotClockedIn = [];
        private $clockedInAndOrScheduled = [];

        public function __construct()
        {
            $this->_init();
            $this->_processScheduleAlerts();
            $this->_processTimeclockAlerts();
        }

        /**
         * Schedule alerts are valid for both clockedin and clockedout emps
         *
         * @return void
         */
        private function _processScheduleAlerts()
        {
            if ($this->_theCurrentScheduleHasBeenPosted()) {
                // merge both employee arrays and send to the schedule alerts
                new ShellScheduleAlerts(
                    array_merge($this->clockedInAndOrScheduled, $this->scheduledNotClockedIn)
                );
            }
        }

        /**
         * Only clocked in employees need to tested against the timeclock
         *
         * @return void
         */
        private function _processTimeclockAlerts()
        {
            new ShellTimeclockAlerts($this->clockedInAndOrScheduled);
        }

        private function _init()
        {
            // acquire the employee values
            $this->scheduledNotClockedIn = $this->_getScheduledAndNotClockedEmps();
            $this->clockedInAndOrScheduled = $this->_getScheduledAndClockedInEmps();
        }

        /**
         *
         *
         * @return array
         */
        private function _getScheduledAndNotClockedEmps()
        {
            return ScheduleShift::whereRaw('DATE(timestamp_start) = ?', [date('Y-m-d')])
                ->whereNotIn('schedule_shifts.employee_id', $this->_clockedIn())
                ->leftJoin('employees as b', 'b.id', 'employee_id')
                ->get(
                    [
                        'schedule_shifts.timestamp_start',
                        'schedule_shifts.timestamp_end',
                        'schedule_shifts.schedule_id',
                        'schedule_shifts.id as shift_id',
                        'b.first_name',
                        'b.last_name',
                        'b.id as employee_id',
                        CDB::raw('UNIX_TIMESTAMP(schedule_shifts.timestamp_start) - UNIX_TIMESTAMP() as preceding'),
                        CDB::raw('"clockedout" as status'),
                    ]
                );
        }

        /**
         *
         *
         * @return array
         */
        private function _getScheduledAndClockedInEmps()
        {
            return Shift::where('active', 1)
                ->leftJoin('employees as b', 'b.id', 'ts_shifts.employee_id')
                ->leftJoin('v_employee_timeclock_status as c', 'c.employee_id', 'ts_shifts.employee_id')
                ->get(
                    [
                        'ts_shifts.id as shift_id',
                        'b.first_name',
                        'b.last_name',
                        'b.id as employee_id',
                        'c.unix_ts as action_timestamp',
                        'c.activity_id',
                        CDB::raw('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(c.time_stamp) as action_duration'),
                        CDB::raw('"clockedin" as status'),
                        CDB::raw('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(ts_shifts.created_at) as shift_duration'),
                    ]
                );
        }

        /**
         *
         *
         * @return void
         */
        private function _clockedIn()
        {
            $x = EmployeeTimeclockStatus::where('activity_id', '!=', 0)->get('employee_id');
            return $x - pluck('employee_id')->toArray();
        }

        private function _theCurrentScheduleHasBeenPosted()
        {
            return Schedule::getCurrentScheduleState();
        }
    }
