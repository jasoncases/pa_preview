import { ButtonContainer } from './Components/ButtonContainer/ButtonContainer.js'
import { Status } from './Components/MicroComponents/Status.js'
import { CWTStatus } from './Components/MicroComponents/CWTStatus.js'
import { CWTIcon } from './Components/MicroComponents/CWTIcon.js'
import { CWTIconMobile } from './Components/MicroComponents/CWTIconMobile.js'
import { TimelineController } from './Components/Timeline/TimelineController.js'
import { CustomWorkTypes } from '../../CustomWorkTypes/src/CustomWorkTypes.js'
import { Button } from '../../System/Components/Button/Button.js'
import { Timeline } from './Components/Timeline/Timeline.js'
import { DisplayHours } from './Components/MicroComponents/DisplayHours.js'

export class Timesheets {
  ButtonContainer: ButtonContainer
  Status: Status
  CWTStatus: Status
  CWTIcon: CWTIcon
  CWTIconMobile: CWTIcon
  TimelineController: TimelineController
  CustomWorkTypes: CustomWorkTypes
  DisplayHours: DisplayHours

  _buttonContainer: HTMLElement
  public constructor() {
    this._init()
  }

  private _init() {
    this._registerAll()
  }

  private _registerAll() {
    this._registerButtonContainer()
    this._registerStatusContainer()
    this._registerCWTStatusContainer()
    this._registerCWTIcons()
    this._registerTimelineController()
    this._registerCustomWorkTypeModule(new CustomWorkTypes())

    this._registerDisplayHours(new DisplayHours())
  }

  private _createButtonContainer(ButtonContainer: ButtonContainer) {
    return ButtonContainer
  }

  private _registerDisplayHours(DisplayHours: DisplayHours) {
    this.DisplayHours = DisplayHours
  }
  private _registerButtonContainer() {
    if (!this._buttonContainerExists()) return
    this.ButtonContainer = this._createButtonContainer(new ButtonContainer())
    this.ButtonContainer.Timesheets = this
  }

  private _registerStatusContainer() {
    if (!this._statusContainerExists()) return
    this.Status = new Status()
    this.Status.init()
  }

  private _registerCWTStatusContainer() {
    if (!this._cwtStatusContainerExists()) return
    this.CWTStatus = new CWTStatus()
    this.CWTStatus.init()
  }

  private _registerCWTIcons() {
    this._registerMobileCWTIcon()
    this._registerCWTIcon()
  }

  private _registerCWTIcon() {
    if (!this._cwtIconExists()) return
    this.CWTIcon = new CWTIcon()
    this.CWTIcon.init()
  }
  private _registerMobileCWTIcon() {
    if (!this._cwtIconMobileExists()) return
    this.CWTIconMobile = new CWTIconMobile()
    this.CWTIconMobile.init()
  }

  private _registerTimelineController() {
    if (!this._timelineExists()) return
    this.TimelineController = this._createTimelineController(
      new TimelineController(),
    )
  }
  private _createTimelineController(TimelineController: TimelineController) {
    return TimelineController
  }

  private _registerCustomWorkTypeModule(CustomWorkTypes: CustomWorkTypes) {
    // this.CustomWorkTypes = CustomWorkTypes;
  }
  private _buttonContainerExists() {
    return document.getElementById('buttons-container')
  }
  private _statusContainerExists() {
    return document.getElementById('status-container')
  }
  private _cwtStatusContainerExists() {
    return document.getElementById('CWT-status-container')
  }
  private _cwtIconExists() {
    return document.getElementById('CWT-icon')
  }
  private _cwtIconMobileExists() {
    return document.getElementById('CWT-icon-mobile')
  }
  private _timelineExists() {
    return document.getElementById('timeline-container')
  }
}
