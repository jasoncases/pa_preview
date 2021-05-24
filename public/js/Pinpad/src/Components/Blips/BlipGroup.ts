import { Component } from '../../../../System/Components/Component/Component.js';
import { Pinpad } from '../../Pinpad.js';
import { Blip } from './Blip.js';

export class BlipGroup extends Component {
  // Component pointers;
  Pinpad: Pinpad;

  // Protected props
  _id: string = 'pinpad-blip-container';
  _blips: Array<Blip> = [];
  _config: any = {};

  public constructor() {
    super();

    this._config = {
      codeLength: null,
    };
  }

  public setCodeLengthSetting(number: number) {
    this._config.codeLength = number;
  }

  protected _registerAll() {
    this._registerElementById();
    this._registerBlips();
    console.log('this BLipGRoup: ', this);
  }

  protected _registerBlips() {
    for (var ii = 0; ii < this._config.codeLength; ii++) {
      this._blips.push(this._createBlip(new Blip()));
    }
  }

  protected _createBlip(Blip: Blip) {
    Blip.registerTargetElementByParent(this._element);
    Blip.init();
    return Blip;
  }

  public render() {
    this._clear();
    this._render();
  }

  protected _render() {
    for (var ii = 0; ii < this.Pinpad.getCodeLength(); ii++) {
      const currBlip = this._blips[ii];
      currBlip.setActive();
    }
  }

  public clear() {
    this._clear();
  }

  protected _clear() {
    this._blips.forEach((blip) => {
      blip.setInactive();
    });
  }

  public error() {
    this._error();
  }

  protected _error() {
    this._blips.forEach((blip) => {
      blip.setError();
    });
  }
}
