import { User } from '../../../../User/src/User.js'
import { TimesheetButton } from '../Button/Button.js'
import { Timesheets } from '../../Timesheets.js'
import { UserStatusBreakStatus, UserStatusCurrentStatusInterface } from '../../../../Proaction/src/InterfaceLib.js'

export class ButtonContainer {
   User: User
   Timesheets: Timesheets
   _buttonContainer: HTMLElement

   public constructor() {
      this._init()
   }

   private _init() {
      this._registerAll()
   }

   private _registerAll() {
      this._registerButtonContainer()
      this._registerAsListenerInUser()
   }

   private _registerButtonContainer() {
      this._buttonContainer = document.getElementById(`buttons-container`)
   }

   private _registerAsListenerInUser() {
      User.registerListener(this)
   }

   public update() {
      this._updateState()
   }

   private _updateState() {
      const state = <UserStatusCurrentStatusInterface>User.currentStatus()
      this._determineState(state.activityId)
   }

   private _determineState(state: number) {
      if (state > 1) {
         return this._createActiveState()
      } else if (state === 0) {
         return this._createClockOutState()
      } else {
         return this._createClockInState()
      }
   }

   private _clearButtons() {
      this._buttonContainer.innerHTML = ''
   }

   private _createClockOutState() {
      this._clearButtons()
      this._createClockOutStateButtons()
   }

   private _createClockInState() {
      this._clearButtons()
      this._createClockInStateButtons()
   }

   private _createActiveState() {
      this._clearButtons()
      this._createActiveStateButtons()
   }

   private _createClockOutStateButtons() {
      this._createButton(
         new TimesheetButton({
            innerText: 'Clock In',
            id: 'clock_in_id_1',
            classList: ['btn', 'btn-clock'],
            actionId: 1,
            employeeId: User.get('employeeId'),
         }),
      )
   }

   private _createClockInStateButtons() {
      this._createButton(
         new TimesheetButton({
            innerText: 'Clock Out',
            id: 'clock_out_id_0',
            classList: ['btn', 'btn-clock'],
            actionId: 0,
            employeeId: User.get('employeeId'),
         }),
      )
      let b = this._createButton(
         new TimesheetButton({
            innerText: 'Start Break',
            id: 'start_break_id_0',
            classList: ['btn', 'btn-clock'],
            actionId: 3,
            employeeId: User.get('employeeId'),
         }),
      )
      let c = this._createButton(
         new TimesheetButton({
            innerText: 'Start Lunch',
            id: 'start_lunch_id_0',
            classList: ['btn', 'btn-clock'],
            actionId: 5,
            employeeId: User.get('employeeId'),
         }),
      )
      this._setDisabled(b, 'breakStatus')
      this._setDisabled(c, 'lunchStatus')
   }

   private _setDisabled(button: TimesheetButton, status: string) {
      console.log('testing: ', User.getInstance(), User.breakStatus())
      const breakStatus = <UserStatusBreakStatus>User.breakStatus()
      if (breakStatus[status] === 'disabled') {
         button.setDisabled(['btn-clock-inactive'])
      }
   }

   private _createActiveStateButtons() {
      const currentState = <UserStatusCurrentStatusInterface>User.currentStatus()
      const text = `End ${currentState.text.replace('Start ', '')}`
      this._createButton(
         new TimesheetButton({
            innerText: text,
            // >>> idkwtfiwd, send help.
            id: `${text.toLowerCase().replace(' ', '_')}_id_1`,
            classList: ['btn', 'btn-clock'],
            actionId: -currentState.activityId, // invert the active state
            employeeId: User.get('employeeId'),
         }),
      )
   }

   private _createButton(Button: TimesheetButton) {
      return Button
   }
}
