import { Component } from '../../../../System/Components/Component/Component.js'
import { Login } from '../../Login.js'

export class Pass extends Component {
  Login: Login
  _id: string = 'ui:login:password';
  _element: any
  _toggle: HTMLButtonElement
  _isPass: boolean = true;

  public constructor() {
    super()
    this._init()
  }

  protected _registerAll() {
    this._registerElementById()
    this._registerToggle()
  }

  private _registerToggle() {
    this._toggle = <HTMLButtonElement>(
      document.getElementById('ui:login:showToggle')
    )
  }

  protected _extendListeners() {
    this._toggle.addEventListener('click', (event) => this._toggleShow(event))
  }

  private _toggleShow(event: MouseEvent) {
    this._isPass = !this._isPass // toggle the boolean
    this._toggleNodeType()
    console.log('testing toggle show: ', this._isPass)
  }

  /**
   * Handle the toggle state switch
   */
  private _toggleNodeType() {
    if (this._isPass) {
      console.log('calling hide')
      this._hidePass()
    } else {
      console.log('calling show')
      this._showPass()
    }
  }

  private _showPass() {
    this._element.type = 'text'
    this._toggle.innerHTML = '<i class="fas fa-eye-slash"></i>'
  }

  private _hidePass() {
    this._element.type = 'password'
    this._toggle.innerHTML = '<i class="fas fa-eye"></i>'
  }

  public getValue() {
    return this._element.value
  }

  public error() {
    console.log('pass.error()')
    this._element.value = ''
  }
}
