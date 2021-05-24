import { Submit } from './Components/Button/Submit.js'
import { Email } from './Components/Text/Email.js'
import { Pass } from './Components/Text/Pass.js'
import { Action } from '../src/Components/Action/Action.js'
import { isJSON, FetchResponseInterface } from '../../System/Lib/Lib.js'
import { Response } from '../../System/Components/Response/Response.js'
import { Session } from '../../System/Components/Session/Session.js'

interface LoginResponseInterface {
  status: boolean
  message: string
}

export class Login {
  Submit: Submit
  Email: Email
  Pass: Pass
  Session: Session

  config: any
  redirectUrl: string

  firstLoginUrl: string = '/user/forcereset';

  public constructor() {
    this._init()
    this.config = {
      url: '/landing',
    }
    console.log('LogiN: ', this)
  }

  private _init() {
    this._registerAll()
  }

  private _registerAll() {
    this._registerSession(Session.getInstance())
    this._registerAsListenerInSession()
    this._registerSubmit(new Submit())
    this._registerEmail(new Email())
    this._registerPass(new Pass())
    this._focusEmail()
  }

  private _focusEmail() {
    this.Email.setFocus()
  }

  private _registerAsListenerInSession() {
    this.Session.registerListener(this, 'REDIRECT')
  }

  public update() {
    this._registerRedirectUrl()
  }

  /**
   * Check for session redirect, or set to default login URL
   */
  private _registerRedirectUrl() {
    console.log('SESSION AT LOGIN: ', this.Session)
    if (typeof this.Session.session.REDIRECT !== 'undefined') {
      this.redirectUrl = this.Session.session.REDIRECT
    } else {
      this.redirectUrl = this.config.url
    }
  }

  private _registerSession(Session: Session) {
    this.Session = Session
  }

  private _registerSubmit(Submit: Submit) {
    this.Submit = Submit
    Submit.Login = this
  }

  private _registerEmail(Email: Email) {
    this.Email = Email
    Email.Login = this
  }

  private _registerPass(Pass: Pass) {
    this.Pass = Pass
    Pass.Login = this
  }

  private _loginPass(data) {
    if (data.firstLogin) {
      window.location.href = this.firstLoginUrl
    } else {
      window.location.href = this.redirectUrl
    }
  }

  private _loginFail() {
    this.Email.error()
    this.Pass.error()
    Response.put(
      'danger',
      'Incorrect login credentials provided. Please try again.',
    )
  }

  public submit() {
    if (!this._validate()) return
    this._action().then((response) => {
      if (response.status === 'success') {
        this._loginPass(response.data)
      } else {
        this._loginFail()
      }
    })
  }

  protected _validate() {
    if (this.Email.getValue() && this.Pass.getValue()) {
      return true
    }
    Response.put('danger', 'Email and password must not be empty')
    return false
  }

  protected _handleResponse(data: LoginResponseInterface) {
    console.log('data:', data)
    if (data.status) {
      this._loginPass(data)
    } else {
      this._loginFail()
    }
  }
  //
  protected _action(): Promise<FetchResponseInterface> {
    return Action.submit(this.Email.getValue(), this.Pass.getValue())
  }
}
