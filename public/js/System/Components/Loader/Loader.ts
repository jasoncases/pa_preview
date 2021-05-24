import {applyCSSClasses, get} from '../../Lib/Lib.js';

export class Loader {
  private static instance;
  _element: HTMLElement;
  _id: string = 'ui:system:loader';
  _classList: Array<string> = ['paLoader'];
  _config: any = {};
  _target: HTMLElement;
  isOpen: boolean = false;
  loaderTimeout: any;

  private constructor() {
    this._registerElement();
    this._registerTarget();
    this._extendElement();
  }

  private _registerTarget() {
    this._target = <HTMLElement>get('content');
  }

  private _registerElement() {
    this._element = document.getElementById(this._id);
  }

  private _extendElement() {
    applyCSSClasses(this._element, this._classList);
    this._setLoaderLocation();
  }

  private _setLoaderLocation() {
    const containerBounds = this._target.getBoundingClientRect();
    const elBounds = this._element.getBoundingClientRect();
    this._element.style.top = `${
      containerBounds.top + containerBounds.height / 2 - elBounds.height / 2
    }px`;
    this._element.style.left = `${
      containerBounds.left + containerBounds.width / 2 - elBounds.width / 2
    }px`;
  }

  public static getInstance() {
    if (!Loader.instance) {
      Loader.instance = new Loader();
    }
    return Loader.instance;
  }

  public static show(delay: number = 0, duration: number = null) {
    console.log('Loader.show()');
    Loader.getInstance()._show(delay, duration);
  }

  public static hide() {
    console.log('Loader.hide()');
    Loader.getInstance()._hide();
  }

  private _show(delay: number = 0, duration: number = null) {
    if (this.isOpen) return;
    if (delay > 0) {
      this.loaderTimeout = setTimeout(() => {
        this._showLoader(duration);
        clearInterval(this.loaderTimeout);
      }, delay);
    } else {
      this._showLoader(duration);
    }
  }

  private _showLoader(duration: number = null) {
    this._element.style.display = 'block';
    this.isOpen = true;
    if (!duration) return;
    setTimeout(() => {
      this._hideLoader();
    }, duration);
  }

  private _hide() {
    this._hideLoader();
  }

  private _hideLoader() {
    if (this.loaderTimeout) {
      clearInterval(this.loaderTimeout);
    }
    if (!this.isOpen) return;
    this._element.style.display = 'none';
    this.isOpen = false;
  }
}
