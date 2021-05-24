export class Setter {
  _els: Array<any>;

  public constructor() {
    this._init();
  }

  private _init() {
    this._gatherFormElements();
    this._setDefaultValues();
  }

  private _gatherFormElements() {
    this._els = Array.from(document.querySelectorAll('[data-setter]'));
  }

  private _setDefaultValues() {
    this._els.forEach((el) => {
      const setNode = <HTMLInputElement>document.querySelector(`[name="set:${el.name}"]`);
      this._setNodeValue(el, setNode.value);
    });
  }

  private _setNodeValue(node: HTMLInputElement, value: any) {
    if (node.type === 'checkbox' || node.type === 'radio') {
      node.checked = Boolean(parseInt(value));
    } else {
      node.value = value;
    }
    node.disabled = false;
  }
}

new Setter();
