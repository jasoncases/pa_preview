import { UserStatusAccumulativeInterface, UserStatusHoursDailyInterface, UserStatusHoursInterface, UserStatusInterface } from '../../../../Proaction/src/InterfaceLib.js'
import { round, _asMoney } from '../../../../System/Lib/Lib.js'
import { User } from '../../../../User/src/User.js'
import { Action } from '../Action/Action.js'
import { Hours } from './Hours.js'

interface HoursObjectInterface {
    total: string
    break: string
    lunch: string
    total_paid: string
}
interface MetaHoursObjectInterface {
    daily: HoursObjectInterface
    weekly: HoursObjectInterface
    monthly: HoursObjectInterface
}

interface AccumulativeHoursRender {
    totalTimeClockedIn: string
    totalTimeOnBreak: string
    totalTimeAtLunch: string
    totalTimePaid: string
}
export class DisplayHours {
    _hours: MetaHoursObjectInterface
    _beenInitiated: boolean = false;
    rawHours: UserStatusHoursInterface
    dailyHours: any

    accWeekly: AccumulativeHoursRender
    accMonthly: AccumulativeHoursRender
    accDaily: AccumulativeHoursRender

    renderUpdateInterval: 15000 // 15 seconds

    public constructor() {
        this._init()
    }

    private _init() {
        this._registerAsListenerInUser()
    }

    private _registerAsListenerInUser() {
        User.registerListener(this)
    }

    public update(cache: UserStatusInterface) {
        this.rawHours = cache.hours
        this._updateCurrentHoursAndRender()
        if (!this.rawHours.daily.isClockedIn) return
        this._initInterval()
    }

    public _initInterval() {
        setInterval(() => {
            this._updateCurrentHoursAndRender()
        }, this.renderUpdateInterval)
    }

    /**
     * rawHours.daily comes in
     */
    private _updateCurrentHoursAndRender() {
        this.accDaily = this._initDailyHours(this.rawHours.daily)
        this._calculateAccumulativeTime()
        this._update()
    }

    private _accumulateHours(freqType, key) {
        const value = round(parseFloat(this.rawHours[freqType].hours[key] ?? 0) + parseFloat(this.accDaily[key] ?? 0), 2)
        return _asMoney(value)
    }

    private _calculateAccumulativeTime() {
        this.accWeekly = {
            totalTimeAtLunch: this._accumulateHours('weekly', 'totalTimeAtLunch'),
            totalTimeClockedIn: this._accumulateHours('weekly', 'totalTimeClockedIn'),
            totalTimePaid: this._accumulateHours('weekly', 'totalTimePaid'),
            totalTimeOnBreak: this._accumulateHours('weekly', 'totalTimeOnBreak'),
        }
        this.accMonthly = {
            totalTimeAtLunch: this._accumulateHours('monthly', 'totalTimeAtLunch'),
            totalTimeClockedIn: this._accumulateHours('monthly', 'totalTimeClockedIn'),
            totalTimePaid: this._accumulateHours('monthly', 'totalTimePaid'),
            totalTimeOnBreak: this._accumulateHours('monthly', 'totalTimeOnBreak'),
        }
    }

    private _initDailyHours(daily: UserStatusHoursDailyInterface) {
        if (!daily.isClockedIn) return <AccumulativeHoursRender><any>daily.hours
        const now = round(Date.now() / 1000)
        let paidDurr, lunchDurr = 0
        const clockDurr = (now - daily.clockInTimeStamp) / 3600
        if (daily.isUserOnLunch) {
            lunchDurr = (now - daily.lunchOutTimeStamp) / 3600
        }
        paidDurr = round(clockDurr - lunchDurr, 2)
        return {
            totalTimeClockedIn: _asMoney(clockDurr),
            totalTimePaid: _asMoney(paidDurr),
            totalTimeAtLunch: _asMoney(lunchDurr),
            totalTimeOnBreak: _asMoney(daily.hours.totalTimeOnBreak)
        }
    }

    private _update() {
        this._displayMonthlyHours()
        this._displayWeeklyHours()
        this._displayDailyHours()
    }

    private _displayMonthlyHours() {
        this._displayMonthlyHoursPaid()
        this._displayMonthlyHoursTotal()
        this._displayMonthlyHoursBreak()
        this._displayMonthlyHoursLunch()
    }
    private _displayWeeklyHours() {
        this._displayWeeklyHoursPaid()
        this._displayWeeklyHoursTotal()
        this._displayWeeklyHoursBreak()
        this._displayWeeklyHoursLunch()
    }
    private _displayDailyHours() {
        this._displayDailyHoursPaid()
        this._displayDailyHoursTotal()
        this._displayDailyHoursBreak()
        this._displayDailyHoursLunch()
    }


    private _displayDailyHoursPaid() {
        Hours.display(
            'ui:display:hours:daily:paid',
            _asMoney(this.accDaily.totalTimePaid)
        )
    }

    private _displayDailyHoursTotal() {
        Hours.display(
            'ui:display:hours:daily:total',
            _asMoney(this.accDaily.totalTimeClockedIn)
        )
    }

    private _displayDailyHoursBreak() {
        Hours.display(
            'ui:display:hours:daily:break',
            _asMoney(this.accDaily.totalTimeOnBreak)
        )
    }

    private _displayDailyHoursLunch() {
        Hours.display(
            'ui:display:hours:daily:lunch',
            _asMoney(this.accDaily.totalTimeAtLunch)
        )
    }

    // ! WEEKLY ----------------------------------------------------------

    private _displayWeeklyHoursPaid() {
        Hours.display(
            'ui:display:hours:weekly:paid',
            this.accWeekly.totalTimePaid
        )
        Hours.display(
            'ui:display:hours:weekly:paid:desktop',
            this.accWeekly.totalTimePaid
        )
    }

    private _displayWeeklyHoursLunch() {
        Hours.display(
            'ui:display:hours:weekly:lunch',
            this.accWeekly.totalTimeAtLunch
        )
    }

    private _displayWeeklyHoursBreak() {
        Hours.display(
            'ui:display:hours:weekly:break',
            this.accWeekly.totalTimeOnBreak
        )
    }

    private _displayWeeklyHoursTotal() {
        Hours.display(
            'ui:display:hours:weekly:total',
            this.accWeekly.totalTimeClockedIn
        )
    }

    // ! MONTHLY ---------------------------------------------------------

    private _displayMonthlyHoursLunch() {
        Hours.display(
            'ui:display:hours:monthly:lunch',
            this.accMonthly.totalTimeAtLunch
        )
    }


    private _displayMonthlyHoursPaid() {
        Hours.display(
            'ui:display:hours:monthly:paid',
            this.accMonthly.totalTimePaid,
        )
        Hours.display(
            'ui:display:hours:monthly:paid:desktop',
            this.accMonthly.totalTimePaid,
        )
    }

    private _displayMonthlyHoursTotal() {
        Hours.display(
            'ui:display:hours:monthly:total',
            this.accMonthly.totalTimeClockedIn
        )
    }

    private _displayMonthlyHoursBreak() {
        Hours.display(
            'ui:display:hours:monthly:break',
            this.accMonthly.totalTimeOnBreak
        )
    }

}
