import { isJSON } from './../../../../Schedule/src/Components/System/Lib.js'
import { Response } from '../../../../System/Components/Response/Response.js'
import {
    handleFetchResponse,
    FetchResponseInterface,
} from '../../../../System/Lib/Lib.js'
import { Fetch } from '../../../../System/Components/Fetch/Fetch.js'

export class Action {
    /**
     *
     * @param employeeId
     * @param actionId
     */
    public static async timesheetAction(
        employeeId: number,
        actionId: number,
    ): Promise<FetchResponseInterface> {
        if (!Action._checkConnection()) return
        return Fetch.store('/timesheet_actions', {
            employee_id: employeeId,
            activity_id: actionId,
        })
    }

    /**
     *
     * @param id
     * @param time
     * @param oldTime
     */
    public static async updateTimesheet(id, time, oldTime) {
        if (!Action._checkConnection()) return
        return Fetch.update(`/timesheet/${id}`, {
            timestamp: time,
            old_timestamp: oldTime,
        })
    }

    private static _checkConnection() {
        if (!navigator.onLine) {
            alert('No internet connection! Please refresh and try again!')
            return false
        }
        return true
    }

    public static async loadHourData() {
        return Fetch.get('/user_hours')
    }
}
