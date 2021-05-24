import { DTWeek } from './DateTimeWeek.js';

var template = document.createElement('template');

template.innerHTML = `
<style>
      :host {
        position: absolute;
        top: -50%;
        left: -1px;
        z-index: 10000000
      }
      .calendarContainer {
        font-size: 12px;
        height: fit-content;
        width: fit-content;
        display: none;
        flex-direction: column;
        justify-content: flex-start;
        background-color: hsl(210, 15%, 90%);
        border-radius: 3px;
        border: 1px solid hsl(210, 55%, 40%);
        position: relative;
      }
      .open {
          display: flex;
      }
      .calendarRow {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
      }

      .calendarCell {
        height: 27px;
        width: 27px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        /* border-radius: 50%; */
        /* background-color: lightblue; */
        margin: 1px;
        border-radius: 3px;
        user-select: none;
      }
      .dowHeader {
        border-radius: 3px;
        background-color: hsl(210, 55%, 20%);
        color: white;
        padding: 4px;
        margin: 0 auto;
        font-weight: 600;
      }
      .day {
        /* background-color: green; */
        border: 1px solid lightgray;
      }
      .day:hover {
        background-color: hsl(210, 75%, 80%);
        cursor: pointer;
      }
      .headerRow {
        justify-content: space-between;
        flex: 1;
        background-color: hsl(210, 75%, 70%);
      }
      .monthNode {
        font-size: 16px;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0 16px;
        margin: 4px;
        border-radius: 15px;
        color: hsl(210, 55%, 15%);
        background-color: hsl(210, 55%, 40%);
        color: white;
        user-select: none;
      }
      .dowRow {
        background-color: hsl(210, 55%, 40%);
      }
      .navNode {
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin: 8px;
      }
      .navNode > i {
        font-size: 16px;
        cursor: pointer;
        color: hsl(210, 55%, 20%);
      }

      .navNode:hover > i {
        color: white;
      }

      .selected {
          background-color: hsl(210, 75%, 70%);
      }
      .selected:active {
          background-color: hsl(210, 75%, 100%);
      }
      .calendarCloser {
        color: hsl(210, 55%, 20%);
        font-size: 16px;
        position: absolute;
        left: calc(100% + 2px);
        bottom: calc(100% + 2px);
      }
      .calendarCloser:hover { 
        color:   rgb(212, 106, 89);
        cursor: pointer;
      }
</style>
 <div class="calendarContainer" id="mainContainer">
    <div class="calendarCloser" id="closer"><i class="fas fa-times-circle"></i></div>
    <div class="calendarRow headerRow">
      <span class="navNode" id="navLeft"><i class="fas fa-chevron-circle-left"></i></span>
      <span class="monthNode" id="heading">January</span>
      <span class="navNode" id="navRight"><i class="fas fa-chevron-circle-right"></i></span>
    </div>
    <div class="calendarRow dowRow">
      <div class="calendarCell" data-dayNum="0">
        <span class=" dowHeader">Su</span>
      </div>
      <div class="calendarCell" data-dayNum="1">
        <span class=" dowHeader">Mo</span>
      </div>
      <div class="calendarCell" data-dayNum="2">
        <span class=" dowHeader">Tu</span>
      </div>
      <div class="calendarCell" data-dayNum="3">
        <span class=" dowHeader">We</span>
      </div>
      <div class="calendarCell" data-dayNum="4">
        <span class=" dowHeader">Th</span>
      </div>
      <div class="calendarCell" data-dayNum="5">
        <span class=" dowHeader">Fr</span>
      </div>
      <div class="calendarCell" data-dayNum="6">
        <span class=" dowHeader">Sa</span>
      </div>
    </div>
</div>
`;

class DateTimeDate extends HTMLElement {
  static get observedAttributes() {
    return ['open', 'value', 'disabled'];
  }

  get open() {
    return this.hasAttribute('open');
  }

  set open(val) {
    const isOpen = Boolean(val);
    if (isOpen) {
      this.setAttribute('open', '');
    } else {
      this.removeAttribute('open');
    }
  }

  get value() {
    return this.getAttribute('value');
  }

  set value(val) {
    const hasValue = Boolean(val);
    if (hasValue) {
      this.setAttribute('value', val);
    } else {
      this.removeAttribute('value');
    }
  }

  get DateTime() {
    return this.getAttribute('DateTime');
  }

  set DateTime(val) {
    const hasValue = Boolean(val);
    if (hasValue) {
      this.setAttribute('DateTime', val);
    } else {
      this.removeAttribute('DateTime');
    }
  }

  constructor() {
    super();
    this.date = new Date();
    this.matrix = [];
    this._weeks = [];
    this.value = '[]';

    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({ mode: 'open' });

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(template.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot;

    this.dateObject = {
      month: this.date.getMonth() + 1,
      date: this.date.getDate(),
      day: this.date.getDay(),
      year: this.date.getFullYear(),
    };

    this._today = { ...this.dateObject };
    const test = this.monthData();
  }

  // _setPrimaryValue() {
  //   setTimeout(() => {
  //     this._setDefaultValue();
  //   }, 1000);
  // }

  _setDefaultValue() {
    const date = this._today.date;
    const month = this._today.month;
    const year = this._today.year;
    const dateStamp = `${year}-${month < 10 ? `0${month}` : month}-${date < 10 ? `0${date}` : date}`;
    this._addToValue(dateStamp);
    this._highlightCachedValues();
  }

  _init() {
    this._registerAll();
    this._initListeners();
    this.injectFontAwesome();
    this._build();
    this._setHeadingMonthText();
    this._setDefaultValue();
  }

  /**
   * Longer months (30/31days) require 6 full weeks to display all days, determined via the
   * formula below. If length of the month + the DOW of the m/01 divided by 7 is greater than 5
   * it will require an additional week to display the last day or two
   */
  _visibleWeeksRequired() {
    const coef = (this._getLengthOfCurrentMonth() + this._getFirstDayOfCurrentMonth()) / 7;
    return coef > 5 ? 6 : 5;
  }

  /**
   * Reset the array matrix before rebuilding on new month data
   */
  _clearMatrix() {
    this.matrix = [];
  }

  /**
   * Build the date matrix for rendering
   */
  _build() {
    this._clearMatrix();
    // create the matrix w/ two for loops
    for (var ii = 0; ii < this._visibleWeeksRequired(); ii++) {
      this.matrix[ii] = []; // create the "week" level array
      for (var jj = 0; jj < 7; jj++) {
        this.matrix[ii].push(this._getValueObject(ii, jj)); // push the day value to the matrix
      }
    }

    this._clearWeeks();
    this._render();
    this._highlightCachedValues();
  }

  _highlightCachedValues() {
    this._weeks.forEach(wk => {
      wk.highlightCachedValues(JSON.parse(this.value));
    });
  }

  _setParentValue() {
    this.DateTime.date = this.value;
  }

  _addToValue(value) {
    const tmpValue = JSON.parse(this.value);
    tmpValue.push(value);
    this.value = JSON.stringify(tmpValue);
  }

  _removeFromValue(value) {
    const tmpValue = JSON.parse(this.value).filter(val => {
      return val !== value;
    });
    this.value = JSON.stringify(tmpValue);
  }
  //
  assertValue(value) {
    this._addToValue(value);
  }

  removeValue(value) {
    this._removeFromValue(value);
  }

  _getValueObject(x, y) {
    return this._renderDayValue(x, y);
  }

  _createWeek(Week) {
    this._weeks.push(Week);
    Week.DateTimeDate = this;
    return Week;
  }

  _clearWeeks() {
    this._weeks.forEach(wk => {
      wk.remove();
    });
  }

  _render() {
    this.matrix.forEach(x => {
      const week = this._createWeek(new DTWeek(x));
      this._mainContainer.appendChild(week.getElement());
    });
  }
  /**
   * Display updating method for the heading text
   */
  _setHeadingMonthText() {
    this._heading.innerText = this.monthData()[this.dateObject.month].name;
  }
  _renderDayValue(x, y) {
    if (x < 1) {
      if (y < this._getFirstDayOfCurrentMonth()) {
        return this._getBackfillFromPreviousMonth(y);
      }
    }
    return this._getCurrentDayOfCurrentMonth(x, y);
  }

  _getCurrentDayOfCurrentMonth(x, y) {
    if (this._getDayValueByXY(x, y) <= this._getLengthOfCurrentMonth()) {
      const date = this._getDayValueByXY(x, y);
      return { val: date, datestamp: this._getDatestampOfCurrentMonth(date) };
    } else {
      const date = this._getDayValueByXY(x, y) - this._getLengthOfCurrentMonth();
      return { val: date, datestamp: this._getDatestampOfNextMonth(date) };
    }
  }
  _getDayValueByXY(x, y) {
    return 7 * x + y - this._getFirstDayOfCurrentMonth() + 1;
  }
  _getBackfillFromPreviousMonth(x) {
    const currFirstDow = this._getFirstDayOfCurrentMonth();
    const diff = currFirstDow - x - 1;
    const date = this._getLengthOfPreviousMonth() - diff;
    return { val: date, datestamp: this._getDatestampOfPreviousMonthValue(date) };
  }

  _getDatestampOfNextMonth(date) {
    const dispDate = date < 10 ? `0${date}` : date;
    const month = this.dateObject.month + 1 > 12 ? 1 : this.dateObject.month + 1;
    const year = this.dateObject.month + 1 > 12 ? this.dateObject.year + 1 : this.dateObject.year;
    return `${year}-${month < 10 ? `0${month}` : month}-${dispDate}`;
  }
  _getDatestampOfCurrentMonth(date) {
    const dispDate = date < 10 ? `0${date}` : date;
    return `${this.dateObject.year}-${this.dateObject.month < 10 ? `0${this.dateObject.month}` : this.dateObject.month}-${dispDate}`;
  }
  _getDatestampOfPreviousMonthValue(date) {
    const dispDate = date < 10 ? `0${date}` : date;
    const month = this.dateObject.month - 1 < 1 ? 12 : this.dateObject.month - 1;
    const year = this.dateObject.month - 1 < 1 ? this.dateObject.year - 1 : this.dateObject.year;
    return `${year}-${month < 10 ? `0${month}` : month}-${dispDate}`;
  }
  _getLengthOfPreviousMonth() {
    const monthTarget = this.dateObject.month - 1 < 1 ? 12 : this.dateObject.month - 1;
    return this.monthData()[monthTarget].length;
  }
  _getLengthOfCurrentMonth() {
    return this.monthData()[this.dateObject.month].length;
  }
  _getNameOfCurrentMonth() {
    return this.monthData()[this.dateObject.month].name;
  }
  _getFirstDayOfCurrentMonth() {
    return this.monthData()[this.dateObject.month].firstDayOfWeek;
  }

  _registerAll() {
    this._registerMainContainer();
    this._registerNav();
    this._registerHeading();
    this._registerCloser();
  }

  _registerCloser() {
    this._closer = this.srPointer.getElementById('closer');
  }
  _registerHeading() {
    this._heading = this.srPointer.getElementById('heading');
  }
  _registerNav() {
    this._registerLeftNav();
    this._registerRightNav();
  }
  _registerLeftNav() {
    this._leftNav = this.srPointer.getElementById('navLeft');
  }
  _registerRightNav() {
    this._rightNav = this.srPointer.getElementById('navRight');
  }
  _registerMainContainer() {
    this._mainContainer = this.srPointer.getElementById('mainContainer');
  }
  connectedCallback() {
    this._init();
  }

  _initListeners() {
    this._mainContainer.addEventListener('click', event => this._mouseClickContainer(event));
    this._leftNav.addEventListener('click', event => this._navMonth(event, -1));
    this._rightNav.addEventListener('click', event => this._navMonth(event, 1));
    this._headingListeners();
    this._closerListeners();
  }

  _closerListeners() {
    this._closer.addEventListener('click', event => this._closerClick(event));
  }

  _closerClick(event) {
    this.open = !this.open;
    if (this._getValueLength() < 1) {
      this._setDefaultValue();
      // ! This will change dateObject back to cached, today value. If rerender, redisplay to current month
      // Thought process: if defaulting back to todays value, possibly want to see current month.
      // Rerender not implmented. Action felt not quite right, either way.

      // this.dateObject = { ...this._today };
    }
  }

  _headingListeners() {
    this._heading.addEventListener('mouseenter', event => this._headingMouseEnter(event));
    this._heading.addEventListener('mouseleave', event => this._headingMouseLeave(event));
  }

  _headingMouseEnter(event) {
    this._heading.innerText = `${this._getNameOfCurrentMonth()} ${this.dateObject.year}`;
    this._heading.style.fontSize = '12px';
  }
  _headingMouseLeave(event) {
    this._heading.style.fontSize = '16px';
    this._setHeadingMonthText();
  }

  _navMonth(event, iterator) {
    this._setDateObject('month', this._getNextMonth(iterator));
    this._build();
    this._setHeadingMonthText();
  }

  _setDateObject(prop, value) {
    this.dateObject[prop] = value;
  }

  _getNextMonth(iterator) {
    const nextMonth = this.dateObject.month + iterator;
    this._iterateYear(nextMonth);
    return nextMonth > 12 ? 1 : nextMonth < 1 ? 12 : nextMonth;
  }

  _iterateYear(nextMonth) {
    if (nextMonth > 12) {
      this.dateObject.year++;
    } else if (nextMonth < 1) {
      this.dateObject.year--;
    }
  }

  _getValueLength() {
    return JSON.parse(this.value).length;
  }
  _mouseClickContainer(event) {
    // this.open = !this.open;
  }
  /**
   * Listen for changes to the attrs defined in the static method observedAttributes
   * name of value changed is passed, as well as the oldValue and the new Value
   */
  attributeChangedCallback(name, oldValue, newValue) {
    // check the name and run your desired method
    if (name === 'open') {
      this.toggleOpen();
    } else if (name === 'value') {
      this._setParentValue();
    }
  }

  toggleOpen() {
    if (this.open) {
      this._mainContainer.classList.add('open');
    } else {
      this._mainContainer.classList.remove('open');
    }
  }

  monthData() {
    return {
      1: {
        name: 'January',
        firstDayOfWeek: new Date(this.dateObject.year, 0, 1).getDay(),
        length: 31,
      },
      2: {
        name: 'February',
        firstDayOfWeek: new Date(this.dateObject.year, 1, 1).getDay(),
        length: this.isLeapYear() ? 29 : 28,
      },
      3: {
        name: 'March',
        firstDayOfWeek: new Date(this.dateObject.year, 2, 1).getDay(),
        length: 31,
      },
      4: {
        name: 'April',
        firstDayOfWeek: new Date(this.dateObject.year, 3, 1).getDay(),
        length: 30,
      },
      5: {
        name: 'May',
        firstDayOfWeek: new Date(this.dateObject.year, 4, 1).getDay(),
        length: 31,
      },
      6: {
        name: 'June',
        firstDayOfWeek: new Date(this.dateObject.year, 5, 1).getDay(),
        length: 30,
      },
      7: {
        name: 'July',
        firstDayOfWeek: new Date(this.dateObject.year, 6, 1).getDay(),
        length: 31,
      },
      8: {
        name: 'August',
        firstDayOfWeek: new Date(this.dateObject.year, 7, 1).getDay(),
        length: 31,
      },
      9: {
        name: 'September',
        firstDayOfWeek: new Date(this.dateObject.year, 8, 1).getDay(),
        length: 30,
      },
      10: {
        name: 'October',
        firstDayOfWeek: new Date(this.dateObject.year, 9, 1).getDay(),
        length: 31,
      },
      11: {
        name: 'November',
        firstDayOfWeek: new Date(this.dateObject.year, 10, 1).getDay(),
        length: 30,
      },
      12: {
        name: 'December',
        firstDayOfWeek: new Date(this.dateObject.year, 11, 1).getDay(),
        length: 31,
      },
    };
  }
  /**
   * Check if this.dateObject.year is a leap year
   *
   * @return bool
   */
  isLeapYear() {
    return this.dateObject.year % 4 === 0;
  }

  injectFontAwesome() {
    const link = document.createElement('link');
    link.setAttribute('rel', 'stylesheet');
    link.setAttribute('href', 'https://pro.fontawesome.com/releases/v5.8.1/css/all.css');
    link.setAttribute('integry', 'sha384-Bx4pytHkyTDy3aJKjGkGoHPt3tvv6zlwwjc3iqN7ktaiEMLDPqLSZYts2OjKcBx1');
    link.setAttribute('crossorigin', 'anonymous');
    this.srPointer.appendChild(link);
  }
}

export default DateTimeDate;
