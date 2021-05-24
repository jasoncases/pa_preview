import { DTDay } from './DateTimeDay.js';

export class DTWeek {
  constructor(arrayOfValues) {
    this.DateTimeDate = null;
    this._days = [];
    this._values = [...arrayOfValues];
    this._init();
  }

  _init() {
    this._registerAll();
    this._createDays();
    this._appendDays();
  }

  _registerAll() {
    this._registerElement();
  }

  _registerElement() {
    this._element = document.createElement('div');
    this._element.classList.add('calendarRow', 'dateRow');
  }
  _createDays() {
    this._values.forEach(val => {
      this._createDayComponent(new DTDay(val));
    });
  }

  _appendDays() {
    this._days.forEach(day => {
      this._element.appendChild(day.getElement());
    });
  }
  _createDayComponent(Day) {
    this._days.push(Day);
    Day.Week = this;
  }

  getElement() {
    return this._element;
  }

  remove() {
    this._element.remove();
  }

  getSelected() {
    return this._days.filter(day => {
      return day.getSelected();
    });
  }

  assertValue(value) {
    this.DateTimeDate.assertValue(value);
  }

  removeValue(value) {
    this.DateTimeDate.removeValue(value);
  }

  highlightCachedValues(arrayOfValues) {
    this._days.forEach(day => {
      if (arrayOfValues.indexOf(day.getDatestamp()) >= 0) {
        day.setSelected();
      }
    });
  }
}
