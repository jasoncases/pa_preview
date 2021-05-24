import { Component } from '../../../../System/Components/Component/Component.js';
import { Login } from '../../Login.js';

export class Email extends Component {
  Login: Login;
  _id: string = 'ui:login:email';

  _element: any;
  public constructor() {
    super();
    this._init();
  }

  protected _registerAll() {
    this._registerElementById();
  }

  public getValue() {
    console.log('testing _lement', this._element);
    return this._element.value;
  }

  public error() {
    this._element.err = 'auth';
    this._element.value = '';
  }

  public setFocus() {
    console.log('setting focus to this element: ', this._element);
    this._element.focus();
  }
}
