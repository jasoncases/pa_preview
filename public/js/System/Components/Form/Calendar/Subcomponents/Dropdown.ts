import {CalendarChildComponent} from './CalendarChildComponent.js';
import {months} from '../../../../Lib/DateHelpers.js';
import {CalendarGrid} from './CalendarGrid.js';

export interface DateObjectInterface {
  day: number;
  month: number;
  year: number;
  primary: number;
}

export class Dropdown extends CalendarChildComponent {
  _id: string = 'calendarBody';

  Grid: CalendarGrid;

  matrix: Array<Array<number>> = [[], [], [], [], [], []];
  daysInAWeek: number = 7;

  day: number;
  month: number;
  year: number;
  week: number;
  dow: number;
  primary: number;

  _prev: DateObjectInterface = {
    day: null,
    month: null,
    year: null,
    primary: null,
  };
  _next: DateObjectInterface = {
    day: null,
    month: null,
    year: null,
    primary: null,
  };

  _displayMonth: HTMLElement;

  _monthData: any;

  protected _extendRegister() {
    this._registerMonthDataObj();
    this._registerDisplayHeader();
    this._registerDefaultDate();
    this._registerGrid(new CalendarGrid());
  }

  private _registerDefaultDate() {}

  private _registerMonthDataObj() {
    this._monthData = months(this.year);
  }
  protected _registerDisplayHeader() {
    console.log('this._element:', this._element);
    this._displayMonth = this._element.querySelector('[id="month"]');
  }

  protected _registerGrid(CalendarGrid: CalendarGrid) {
    //
    this.Grid = CalendarGrid;
    CalendarGrid.registerTargetElementByParent(this._element);
    CalendarGrid.init();
  }

  public setMonth(num: number) {
    this.month = num;
  }

  public setDay(num: number) {
    this.day = num;
  }

  public setDow(num: number) {
    this.dow = num;
  }

  public setYear(num: number) {
    this.year = num;
  }

  private _currDateObj() {
    return {
      day: this.day,
      month: this.month,
      year: this.year,
      primary: this.primary,
    };
  }

  public setDate(dateObj: DateObjectInterface) {
    this.setDay(dateObj.day);
    this.setMonth(dateObj.month);
    this.setYear(dateObj.year);
    this._reset();
    console.log('DROP DOWN>>>>>>>>', this);
  }

  private _reset() {
    this._setPreviousMonth();
    this._setNextMonth();
    this._setDisplayHeader();
    this._setPrimaryValue();
    this._updateGrid();
  }

  private _updateGrid() {
    this.Grid.updateGrid(this._currDateObj(), this._prev, this._next);
  }

  private _setPrimaryValue() {
    this.primary = this._monthData[this.month].firstDayOfWeek;
  }

  private _setDisplayHeader() {
    this._displayMonth.innerText = this._monthData[this.month].name;
  }

  private _setPreviousMonth() {
    this._prev.day = 1;
    if (this.month - 1 < 1) {
      this._prev.month = 12;
      this._prev.year = this.year - 1;
    } else {
      this._prev.month = this.month - 1;
      this._prev.year = this.year;
    }
  }

  private _setNextMonth() {
    this._next.day = 1;
    if (this.month + 1 > 12) {
      this._next.month = 1;
      this._next.year = this.year + 1;
    } else {
      this._next.month = this.month + 1;
      this._next.year = this.year;
    }
  }
}
