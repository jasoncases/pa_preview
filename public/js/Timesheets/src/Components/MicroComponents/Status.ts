import { UserStatusCurrentStatusInterface } from '../../../../Proaction/src/InterfaceLib.js'
import { User } from '../../../../User/src/User.js'

export class Status {
  User: User
  _element: HTMLElement
  _id: string

  public constructor() {
    //
    this._id = 'status-container'
  }

  public init() {
    this._init()
  }
  private _init() {
    this._registerAll()
  }

  private _registerAll() {
    this._registerElement()
    this._registerAsListenerInUser()
  }

  private _registerElement() {
    this._element = document.getElementById(this._id)
  }


  private _registerAsListenerInUser() {
    User.registerListener(this)
  }

  public update() {
    const status = <UserStatusCurrentStatusInterface>User.currentStatus()
    this._updateColor(status.color)
    this._updateText(status.readable)
  }

  private _updateColor(color: string) {
    this._element.style.backgroundColor = color
    this._element.style.color = 'hsl(0, 0%, 0%)'
  }

  private _updateText(text: string) {
    this._element.innerText = text
  }
}
