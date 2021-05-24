import {Component} from '../../../Component/Component.js';
import {Calendar} from '../Calendar.js';

export class CalendarChildComponent extends Component {
  Calendar: Calendar;
  _id: string;
  public constructor(Calendar: Calendar) {
    super();
    this.Calendar = Calendar;
  }

  protected _registerAll() {
    this._registerFromCalendarQuerySelector();
  }

  protected _registerFromCalendarQuerySelector() {
    const parentElement = this.Calendar.getElement();
    console.log('parentElement:', parentElement);
    this._element = parentElement.querySelector(`[id="${this._id}"]`);
    console.log('this._element:', this._element);
  }
}
