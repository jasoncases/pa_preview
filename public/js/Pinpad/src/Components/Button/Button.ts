import { Component } from '../../../../System/Components/Component/Component.js';
import { applyCSSClasses } from '../../../../System/Lib/Lib.js';
import { Keypad } from '../Keypad/Keypad.js';

export interface PinButtonConfig {
  action: string;
  id: string;
}

export class PinButton extends Component {
  Keypad: Keypad;
  _id: string = '';
  _parentId: string = 'keypad';
  _config: PinButtonConfig;
  public constructor(btnConfig: PinButtonConfig) {
    super();

    this._id = btnConfig.id;
    this._config = { ...btnConfig };
    this._init();
  }

  protected _registerAll() {
    this._registerElementById();
  }

  protected _extendElement() {
    this._element.setAttribute('data-action', this._config.action);
  }

  protected _extendInit() {
    this._registerParentById();
  }

  protected _initListeners() {
    this._element.addEventListener('click', (event) => this._mouseClickContainer(event));
  }

  protected _action(event: MouseEvent) {
    const action = this._getAction();
    if (typeof this[action] === 'function') {
      this[action]();
    } else {
      this.Keypad.addCode(parseInt(action));
    }
  }

  protected delete() {
    this.Keypad.removeCode();
  }

  protected submit() {
    this.Keypad.submit();
  }

  protected _getAction() {
    return this._element.getAttribute('data-action');
  }
}
