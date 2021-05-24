export class Validator {
  element: HTMLInputElement;
  re: RegExp;
  key: string;
  focus: boolean;
  public constructor(id: string, re?: RegExp, key?: string, focus: boolean = false) {
    this.element = this._registerElementById(id);
    this.re = re;
    this.key = key;
    this.focus = focus;
    this._init();
  }

  private _init() {
    if (this.re === null) {
      this.re = this._createRegExpByKey(this.key);
    }
  }

  public process() {
    if (this._validate()) {
      console.warn('validator process = true');
      return true;
    } else {
      console.warn('validator process = false');
      return this._handleFailure();
    }
  }

  private _handleFailure() {
    if (this.focus) {
      this.element.addEventListener('focus', (event) => {
        console.log('testing');
        this.element.setAttribute('style', 'border: 2px solid red;');
      });
      this.element.focus();
    }
    return false;
  }

  private _validate() {
    const valueToValidate = this.element.value;
    const matchX = valueToValidate.match(this.re);
    console.log('matchX:', matchX, this.re);
    return valueToValidate.match(this.re);
  }

  private _registerElementById(id: string) {
    return <HTMLInputElement>document.getElementById(id);
  }

  private _createRegExpByKey(key: string) {
    switch (key) {
      case 'phone':
        return this._phone();
      case 'phoneDash':
        return this._phoneDash();
      case 'email':
        return this._email();
    }
  }

  private _phoneDash() {
    return new RegExp(/[0-9]{3}\-[0-9]{3}\-[0-9]{4}/);
  }
  private _phone() {
    return new RegExp(/[0-9]+/);
    // return new RegExp(/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im);
  }
  private _email() {
    return new RegExp(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
  }
}
