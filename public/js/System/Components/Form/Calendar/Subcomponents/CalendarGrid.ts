import {Component} from '../../../Component/Component.js';
import {Dropdown, DateObjectInterface} from './Dropdown.js';

export class CalendarGrid extends Component {
  _id: string = 'calendarContainer';
  Dropdown: Dropdown;
  matrix: Array<Array<string>> = [[], [], [], [], [], []];

  protected _registerElement() {
    this._element = this._target.querySelector(`[id="${this._id}"]`);
  }

  public updateGrid(
    currMon: DateObjectInterface,
    prevMon: DateObjectInterface,
    nextMonth: DateObjectInterface,
  ) {}
}
