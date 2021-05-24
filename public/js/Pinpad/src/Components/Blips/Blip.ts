import { Component } from '../../../../System/Components/Component/Component.js';
import { BlipGroup } from './BlipGroup.js';
import Creator from '../../../../System/Components/Creator/Creator.js';
import { applyCSSClasses } from '../../../../System/Lib/Lib.js';

export class Blip extends Component {
  BlipGroup: BlipGroup;
  Creator: Creator;

  _config: any = {};
  public constructor() {
    super();

    this._config = {
      active: 'blip-selected',
      error: 'blip-shake',
      blipClasses: ['blip'],
    };
  }

  public _registerAll() {
    console.log('LBip: ', this);
    this._registerCreator(new Creator());
    this._registerElement();
    this._appendElementToTarget();
  }

  protected _registerCreator(Creator: Creator) {
    this.Creator = Creator;
  }

  protected _registerElement() {
    this._element = this.Creator.createElement('div', {});
    applyCSSClasses(this._element, this._config.blipClasses);
  }

  public setActive() {
    this._element.classList.add(this._config.active);
  }

  public setInactive() {
    this._element.classList.remove(this._config.active);
  }

  public setError() {
    this.setInactive();
    this._shake();
  }

  protected _shake() {
    this._element.classList.add(this._config.error);
    this._removeShake();
  }

  protected _removeShake() {
    setTimeout(() => {
      this._element.classList.remove(this._config.error);
    }, 1200);
  }
}
