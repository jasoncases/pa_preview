import { Pinpad } from '../../Pinpad.js';

interface CodeInterface {
  //
  Pinpad: Pinpad;
  _code: Array<number>;
}

export class Code implements CodeInterface {
  // Component pointers
  Pinpad: Pinpad;

  // Protected props
  _code: Array<number>;
  _config: any = {};

  constructor() {
    this._code = [];
    this.Pinpad = null;
    this._config = {
      codeLength: null,
    };
  }

  public setCodeLengthSetting(number: number) {
    this._config.codeLength = number;
  }

  public addCode(num) {
    if (this.getLength() >= this._config.codeLength) return;
    this._code.push(num);
    console.log('code: ', this._code);
  }

  public removeCode() {
    if (this.getLength() >= this._config.codeLength) {
      this.clear();
    } else {
      this._code.pop();
    }
    console.log('code: ', this._code);
  }

  public getCode() {
    return this._code;
  }

  public clear() {
    this._code.length = 0;
  }

  public getLength() {
    return this._code.length;
  }

  public encodedOutput() {
    try {
      const code = this._code.join('');
      return window.btoa(code);
    } catch (e) {
      console.error(e);
    }
  }
}
