import { Component } from '../../../../System/Components/Component/Component.js';
import { Pinpad } from '../../Pinpad.js';
import { PinButton } from '../Button/Button.js';
import { ButtonFactory } from '../Button/ButtonFactory.js';

export class Keypad extends Component {
  Pinpad: Pinpad;
  _id: string = 'keypad';

  _arrayOfKeyIds: Array<string> = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'delete', 'submit'];
  _keys: Array<PinButton> = [];

  public constructor() {
    super();
  }

  protected _registerAll() {
    this._registerElementById();
    this._registerButtons();
    this._registerKeyEventListener();
  }

  private _registerKeyEventListener() {
    window.addEventListener('keydown', (e) => this._keyPress(e));
  }

  private _keyPress(event: any) {
    if (event.keyCode === 46 || event.keyCode === 8) {
      this.Pinpad.removeCode();
    }
    if (event.keyCode >= 48 && event.keyCode <= 57) {
      this.Pinpad.addCode(parseInt(event.key));
    }
    if (event.keyCode >= 96 && event.keyCode <= 109) {
      this.Pinpad.addCode(parseInt(event.key));
    }
  }

  protected _registerButtons() {
    this._arrayOfKeyIds.forEach((key) => {
      const btn = ButtonFactory.createButton({
        id: key,
        action: key,
      });
      btn.Keypad = this;
      this._keys.push(btn);
    });
  }

  public addCode(code: number) {
    this.Pinpad.addCode(code);
  }
  public removeCode() {
    this.Pinpad.removeCode();
  }

  public submit() {
    this.Pinpad.submit();
  }
}
