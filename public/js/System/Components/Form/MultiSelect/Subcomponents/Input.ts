import {Component} from '../../../Component/Component.js';
import {MultiSelect} from '../MultiSelect.js';

export class Input extends Component {
  Parent: MultiSelect;
  _element: HTMLInputElement;
  public constructor() {
    super();
  }

  protected _registerAll() {
    this._registerElement();
  }

  public registerPresets() {
    const value = this._element.value;
    if (value) {
      this.Parent.setDefaultDisplay(value.split(','));
    }
  }

  protected _registerElement() {
    this._element = this._target.querySelector('input');
  }

  public setValue(value: string) {
    this._element.value = value;
  }

  public getValue() {
    return this._element.value;
  }

  public reset() {
    this._element.removeAttribute('value');
  }
}
