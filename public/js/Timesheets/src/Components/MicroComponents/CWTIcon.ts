import { UserStatusCurrentStatusInterface } from '../../../../Proaction/src/InterfaceLib.js'
import { User } from '../../../../User/src/User.js'

export class CWTIcon {
   User: User
   _element: HTMLElement
   _id: string

   public constructor() {
      //
      this._id = 'CWT-icon'
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
   }

   private _updateColor(color: string) {
      // this._element.style.backgroundColor = color;
      this._element.style.color = color
   }
}
