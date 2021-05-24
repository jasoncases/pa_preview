import { applyCSSClasses } from '../../Lib/Lib.js';

export class Alert {
  status: string;
  msg: string;
  fadeTimer: number;
  response: any;
  _id: string = 'flash-status';
  _config: any;

  _element: HTMLElement;
  _textContainer: HTMLElement;

  public constructor(response: any, fade: number = 4000) {
    //
    this._config = {
      classList: ['flash-status', 'dbl-box-shadow'],
    };
    this.response = response;
    this._init();
    this._setText();
    this._setElement();
    this._setFade(fade);
  }

  private _init() {
    this._registerAll();
  }

  private _registerAll() {
    this._getElement();
    this._getTextContainer();
  }

  private _getElement() {
    this._element = document.getElementById(this._id);
    this._resetElement();
  }

  /**
   * Because this doesnt' create an element and just catches an element by id
   * it could be that the Alert box is already in transition. This clears the
   * class list and then reapplies the classlist back to default.
   */
  private _resetElement() {
    this._element.className = '';
    applyCSSClasses(this._element, this._config.classList);
  }

  private _getTextContainer() {
    this._textContainer = document.getElementById('flash-text');
  }

  private _setText() {
    this._textContainer.innerText = this.response.msg;
  }

  private _setElement() {
    this._element.classList.add(`flash-${this.response.status}`);
  }

  private _setFade(timer: number) {
    setTimeout(() => {
      this._removeStatusClass();
    }, timer);
  }

  private _removeStatusClass() {
    this._element.classList.remove(`flash-${this.response.status}`);
  }
}
