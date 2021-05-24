import { RuntimeConfigurationObject } from '../../../../Schedule/src/Components/System/Lib.js'
import Creator from '../../../../Schedule/src/Components/System/Creator.js'
import { Action } from '../Action/Action.js'
import { User } from '../../../../User/src/User.js'
import { Button } from '../../../../System/Components/Button/Button.js'
import { Response } from '../../../../System/Components/Response/Response.js'

interface ButtonInterface {
  _config: RuntimeConfigurationObject
  _parent: HTMLElement
  _element: HTMLButtonElement
  Creator: Creator
}

interface ButtonObjectInterface {
  id: string
  innerText: string
  classList: Array<string>
  actionId: number
  employeeId: number
}

export class TimesheetButton extends Button implements ButtonInterface {
  _config: RuntimeConfigurationObject
  _parent: HTMLElement
  _element: HTMLButtonElement

  Creator: Creator

  public constructor(btnObject: ButtonObjectInterface) {
    super()

    //
    this._config = { ...btnObject }

    this._init()
  }

  protected _registerParent() {
    this._parent = document.getElementById('buttons-container')
  }


  protected _extendElement() {
    this._element.id = this._config.id
    this._element.innerText = this._config.innerText
    this._element.setAttribute('data-employeeId', this._config.employeeId)
    this._element.setAttribute('data-actionId', this._config.actionId)
    this._registerClassList(this._config.classList)
  }
  /**
   * Mouse action container method
   * @param event
   */
  protected _mouseClickContainer(event: MouseEvent) {
    this.setDisabled(['btn-clock-inactive'])
    this._newAction()
  }

  /**
   * Dependency Injection of Action Class
   *
   * @param Action
   */
  protected _newAction() {
    Action.timesheetAction(
      parseInt(this._config.employeeId),
      parseInt(this._config.actionId),
    ).then((response) => {
      this._handleResponse(response)
    })
  }

  protected _handleResponse(response: any) {
    console.log('response:', response)
    if (response.status === 'success') {
      console.warn("We removed a call to User.reload(), so if something is broken, we may need to implement a new version of it")
    }
  }
}
