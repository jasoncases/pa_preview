export class DTDay {
  constructor(value) {
    this._selected = null;
    this.Week = null;
    this._init();
    this._setValue(value);
  }

  _init() {
    this._registerAll();
    this._initListeners();
  }

  _registerAll() {
    this._registerElement();
  }

  _registerElement() {
    this._element = document.createElement('div');
    this._element.classList.add('calendarCell', 'day');
  }
  _setValue(value) {
    this._setDatestamp(value);
    this._element.innerText = value.val;
  }

  _setDatestamp(value) {
    this._datestamp = value.datestamp;
  }

  _initListeners() {
    this._element.addEventListener('click', event => this._mouseClickContainer(event));
  }

  _mouseClickContainer(event) {
    if (event.target !== this._element) return;
    this._selected = !this._selected;
    this._updateDisplay();
    this._updateAssertionState();
  }

  _updateAssertionState() {
    if (this._selected) {
      this.assertValue();
    } else {
      this.removeValue();
    }
  }
  _updateDisplay() {
    if (this._selected) {
      this._element.classList.add('selected');
    } else {
      this._element.classList.remove('selected');
    }
  }

  setSelected() {
    this._selected = true;
    this._updateDisplay();
  }

  getElement() {
    return this._element;
  }

  getSelected() {
    return this._selected;
  }

  getDatestamp() {
    return this._datestamp;
  }

  assertValue() {
    this.Week.assertValue(this.getDatestamp());
  }

  removeValue() {
    this.Week.removeValue(this.getDatestamp());
  }
}
