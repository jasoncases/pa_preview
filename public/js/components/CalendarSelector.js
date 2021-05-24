import TemplateParser from './class/TemplateParser.js';

let outputContainerTemplate = document.createElement('template');
outputContainerTemplate.innerText = `{outputMonth} {date}`;

// Vars --------
/**
 * -webkit-appearance: none;
   border-radius: 5px;
   display: block;
   font-size: 1.6rem;
   font-weight: bold;
   padding: 9px 25px;
   margin: 2px 5px;
   width: fit-content;
   height: fit-content;
   color: white;
   text-align: center;
   border: none;
   pointer-events: all;
   text-decoration: none;
 */
var fontSize = 16;
var vertPadding = 8;
var horzPadding = 25;
var vertMargin = 2;
var horzMargin = 0;
var borderRadius = 5;
var zIndex = 20000;
// Template --------
let calendarBodyTemplate = document.createElement('template');
calendarBodyTemplate.innerHTML = `
<div class="calendarContainer" id="calendarContainer" style="visibility:hidden;">
    <div class="calendarHeader" id="calendarHeader">
    <div class="calendarHeaderNav" id="navLeft" style="transform:rotate(-180deg)">&#10148;</div>
    <div class="calendarHeaderText" id="month-text">{month} {year}</div>
    <div class="calendarHeaderNav" id="navRight">&#10148;</div>
    </div>
    <div class="calendarDayRow">
        <div class="calendarDayCell">Su</div>
        <div class="calendarDayCell">Mo</div>
        <div class="calendarDayCell">Tu</div>
        <div class="calendarDayCell">We</div>
        <div class="calendarDayCell">Th</div>
        <div class="calendarDayCell">Fr</div>
        <div class="calendarDayCell">Sa</div>
    </div>
    <div class="calendarRow calendarRowHover" id="calRow" data-rowNum="0" data-rowStatus="null">
        <div class="calendarCell" id="U"><span id="date-container" data-date="" data-dow="0" class="date-container"></span></div>
        <div class="calendarCell" id="M"><span id="date-container" data-date="" data-dow="1" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="2" class="date-container"></span></div>
        <div class="calendarCell" id="W"><span id="date-container" data-date="" data-dow="3" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="4" class="date-container"></span></div>
        <div class="calendarCell" id="F"><span id="date-container" data-date="" data-dow="5" class="date-container"></span></div>
        <div class="calendarCell" id="S"><span id="date-container" data-date="" data-dow="6" class="date-container"></span></div>
    </div>
    <div class="calendarRow calendarRowHover" id="calRow" data-rowNum="1" data-rowStatus="null">
        <div class="calendarCell" id="U"><span id="date-container" data-date="" data-dow="0" class="date-container"></span></div>
        <div class="calendarCell" id="M"><span id="date-container" data-date="" data-dow="1" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="2" class="date-container"></span></div>
        <div class="calendarCell" id="W"><span id="date-container" data-date="" data-dow="3" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="4" class="date-container"></span></div>
        <div class="calendarCell" id="F"><span id="date-container" data-date="" data-dow="5" class="date-container"></span></div>
        <div class="calendarCell" id="S"><span id="date-container" data-date="" data-dow="6" class="date-container"></span></div>
    </div>
    <div class="calendarRow calendarRowHover" id="calRow" data-rowNum="2" data-rowStatus="null">
        <div class="calendarCell" id="U"><span id="date-container" data-date="" data-dow="0" class="date-container"></span></div>
        <div class="calendarCell" id="M"><span id="date-container" data-date="" data-dow="1" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="2" class="date-container"></span></div>
        <div class="calendarCell" id="W"><span id="date-container" data-date="" data-dow="3" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="4" class="date-container"></span></div>
        <div class="calendarCell" id="F"><span id="date-container" data-date="" data-dow="5" class="date-container"></span></div>
        <div class="calendarCell" id="S"><span id="date-container" data-date="" data-dow="6" class="date-container"></span></div>
    </div>
    <div class="calendarRow calendarRowHover" id="calRow" data-rowNum="3" data-rowStatus="null">
        <div class="calendarCell" id="U"><span id="date-container" data-date="" data-dow="0" class="date-container"></span></div>
        <div class="calendarCell" id="M"><span id="date-container" data-date="" data-dow="1" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="2" class="date-container"></span></div>
        <div class="calendarCell" id="W"><span id="date-container" data-date="" data-dow="3" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="4" class="date-container"></span></div>
        <div class="calendarCell" id="F"><span id="date-container" data-date="" data-dow="5" class="date-container"></span></div>
        <div class="calendarCell" id="S"><span id="date-container" data-date="" data-dow="6" class="date-container"></span></div>
    </div>
    <div class="calendarRow calendarRowHover" id="calRow" data-rowNum="4" data-rowStatus="null">
        <div class="calendarCell" id="U"><span id="date-container" data-date="" data-dow="0" class="date-container"></span></div>
        <div class="calendarCell" id="M"><span id="date-container" data-date="" data-dow="1" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="2" class="date-container"></span></div>
        <div class="calendarCell" id="W"><span id="date-container" data-date="" data-dow="3" class="date-container"></span></div>
        <div class="calendarCell" id="T"><span id="date-container" data-date="" data-dow="4" class="date-container"></span></div>
        <div class="calendarCell" id="F"><span id="date-container" data-date="" data-dow="5" class="date-container"></span></div>
        <div class="calendarCell" id="S"><span id="date-container" data-date="" data-dow="6" class="date-container"></span></div>
    </div>
</div>
`;

let calendarSelector = document.createElement('template');
calendarSelector.innerHTML = `
<style>
:host {
   
    font-family:  'Montserrat', sans-serif;
    -webkit-touch-callout: none;
   -webkit-user-select: none;
   -khtml-user-select: none;
   -moz-user-select: none;
   -ms-user-select: none;
   -o-user-select: none;
   user-select: none;
}


div {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
}

.outerContainer {
    position: relative;
    background-color: rgb(65, 160, 163);
    width: 230px;
    height: 34px;
    display: flex;
    flex-direction: row; 
    justify-content: flex-start;
    border-radius: ${borderRadius}px;
    margin: ${vertMargin}px ${horzMargin}px;
}
.oC-disabled {
   filter: saturate(0.5) brightness(90%);
}
.outerContainer:hover {
    background-color: rgb(112, 206, 209);
}
.oC-disabled {
   filter: saturate(0.5) brightness(90%);
}

.collapsedOutputContainer {
    // background-color: hsla(0, 0%, 98%, 1);
    // border: 1px solid hsla(0, 0%, 93%, 1);
    font-size: 14px;
    color: black;
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    pointer-events: none;
    font-weight: 600;
}

.cOC-text {
    width: 165px;
    font-weight: 500;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    font-size: ${fontSize}px;
    padding: ${vertPadding}px 0 ${vertPadding}px ${horzPadding}px ;
    color: white;
}
.cOC-icon {
   color: white;
   display: flex;
   flex-direction: column;
   justify-content: center;
   text-align: center;
   /* height: 100%; */
   width: 25px;
   margin-left: auto;
}
.calendarContainer {
    position: absolute;
    top: 0;
    left: 0;
    width: 290px;
    height: 270px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 3px;
    border-radius: ${borderRadius}px;
    background-color: hsla(0, 0%, 98%, 1);
    border: 1px solid hsla(0, 0%, 50%, 1);
    z-index: ${zIndex};
   box-shadow: 1px 2px hsla(0, 0%, 0%, 0.25), 1px 4px hsla(0, 0%, 0%, 0.15);

}
.calendarHeader {
    width: 100%;
    height: 32px;
    background-color: hsla(202, 83%, 19%, 1);
    color: white;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    padding: 5px;
    font-size: 16px;
    font-weight: 600;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
}
.calendarHeaderNav {
    height: 100%;
    width: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    cursor: pointer;
}
.calendarHeaderText {
    flex: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    cursor: pointer;
}
.calendarRow {
    flex: 1;
    display: flex;
    flex-direction: row;
    justify-content: space-evenly;
    background-color: hsla(0, 0%, 95%, 1);
}
.calendarRowHover:hover {
    background-color: hsla(213, 25%, 88%, 1);
}
.calendarRow:last-child {
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
}

.calendarRow:last-child > .calendarCell:first-child {
    border-bottom-left-radius: 3px;
}
.calendarRow:last-child > .calendarCell:last-child {
    border-bottom-right-radius: 3px;
}

.calendarDayRow {
    background-color: lightgray;
    font-weight: 700;
    color: black;
    font-size: 14px;
    display: flex;
    flex-direction: row;
    justify-content: space-evenly;
    height: 20px;
    flex:1;
}
.calendarDayCell {
    
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    width: 12%;
    height: 90%;
    font-weigth: 400;
}
.calendarCell {
    color: black;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    color: black;
    font-size: 12px;
    width: 12%;
    height: 100%;
    flex:1;
}
.calendarCell:hover {
    background-color: hsla(213, 40%, 80%, 1);
    border-radius: 3px;
   }
.activeDay {
   font-weight: 800;    
}
.weekSelected {
    
}
.today {
   background-color: hsla(202, 83%, 65%, 1);
   border-radius: 3px;
}
.date-container {
   height: 100%;
   width: 100%;
   display: flex;
   flex-direction: column;
   justify-content: center;
}
.new {

}
.open {
   background-color: #B0DCA9;
}
.pending {
   background-color: #F9F3BC;
}
.commit {
   background-color: #F4CA77;
}
.commit-edit {
   background-color: #F48335;
}
.locked {
   background-color: #FFC5AC;
}
.date-lock {
   background-color: #FF7C44;
}
.caret {
   display: inline-block;
   width: 0;
   height: 0;
   margin: 0 auto;
   vertical-align: middle;
   border-top: 6px solid white;
   border-right: 6px solid transparent;
   border-left: 6px solid transparent;
}

.selected {
   background-color: white;
   border-radius: 50%;
}
.arrow { 
  height: 34px;
  width: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  margin: 0 auto;
}
.arrow:hover {
  background-color: hsla(182, 29%, 40%, 1);
}
.arrow-right {
  transform: rotate(-90deg);
}
.arrow-left {
  transform: rotate(90deg);
}
.left {
  border-right: 1px solid hsla(182, 29%, 67%, 1);
  border-top-left-radius: ${borderRadius}px;
  border-bottom-left-radius: ${borderRadius}px;
  cursor: pointer;
}
.right {
  border-left: 1px solid hsla(182, 29%, 67%, 1);
  border-top-right-radius: ${borderRadius}px;
  border-bottom-right-radius: ${borderRadius}px;
  cursor: pointer;
}
.past {
   background-color: green;
}
</style>
<div class="outerContainer" id="outerContainer">
    <div class="arrow left" id="iterate-left"><span class="caret arrow-left"></span></div>
    <div class="collapsedOutputContainer" id="displayContainer">
    <div class="cOC-text" id="output-text">YYYY/MM/DD - YYYY/MM/DD</div>
    </div>
    <div class="arrow right" id="iterate-right"><span class="caret arrow-right"></span></div>
</div>
`;

/**
 * CalendarSelector custom component for date seletion.
 *
 *
 * optional 'data-' attributes
 * mode =>     (this.mode)          'single' (assumed), 'range', 'weekly', 'range-triple'
 * load =>     (this.load)          if exists, the component will run a load routine to add additional data for status and apply those status as a class to rows (specific edge case pertinent only to me). Will remove for distribution
 * viewport => (this.viewport)      'day' (shows selected day, DEFAULT), 'range' (show selected range as dates w/ following format: YYYY-MM-DD - YYYY-MM-DD)
 * format =>   (this.vpFormat)      Coming soon: allows to change the aforementioned date format
 * active =>   (this.activeDate)    only recognized when mode is 'weekly', if exists, output date as well
 * open =>     (this.open)
 * hold =>     (this.hold)          forces open status to remain
 */

// Modes and settings
/**
 *
 * SINGLE:        output: date
 * RANGE:         output: to, from
 * WEEKLY:        output: to, from (action restricted to week by week)
 * RANGE-TRIPLE:  output: to, from
 *
 */

export class CalendarSelector extends HTMLElement {
  static get observedAttributes() {
    return ['open', 'disabled', 'selectedDate'];
  }
  // getter/setter for this.open
  get open() {
    return this.hasAttribute('open');
  }
  set open(val) {
    if (this.hold) return;
    const isOpen = Boolean(val);
    if (isOpen) {
      this.setAttribute('open', '');
    } else {
      this.removeAttribute('open');
    }
  }

  get disabled() {
    return this.hasAttribute('disabled');
  }

  set disabled(val) {
    if (this.hold) return;
    const hasVal = Boolean(val);
    if (hasVal) {
      this.setAttribute('disabled', '');
    } else {
      this.removeAttribute('disabled');
    }
  }

  get selectedDate() {
    return this.getAttribute('selectedDate');
  }
  set selectedDate(val) {
    if (val) {
      this.setAttribute('selectedDate', val);
    } else {
      this.removeAttribute('selectedDate');
    }
  }
  get hold() {
    return this.hasAttribute('hold');
  }
  set hold(val) {
    const isHeld = Boolean(val);
    if (isHeld) {
      this.setAttribute('hold', '');
    } else {
      this.removeAttribute('hold');
    }
  }
  get mode() {
    return this.getAttribute('data-mode') || 'single';
  }
  get loadStatus() {
    return this.hasAttribute('data-load');
  }
  get activeDate() {
    return this.hasAttribute('data-active');
  }
  get viewport() {
    return this.hasAttribute('data-viewport') || 'day';
  }

  constructor() {
    super();

    // create default dateObject onload
    // if () {
    //    this.date = new Date();
    // } else {
    this.date =
      this.selectedDate === null ? new Date() : new Date(this.selectedDate);

    this.dateObject = {
      month: this.date.getMonth() + 1,
      date: this.date.getDate(),
      day: this.date.getDay(),
      year: this.date.getFullYear(),
    };

    this.longformDowArray = [
      'Sunday',
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
    ];
    this.dowArray = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

    // establish shadowRoot and create an obj-level pointer
    const shadowRoot = this.attachShadow({mode: 'open'});
    shadowRoot.appendChild(calendarSelector.content.cloneNode(true));
    this.srPointer = shadowRoot;

    // set the outputContainerTemplate content to a main property so we can easily update this font
    this.outputContainerTemplate = outputContainerTemplate.innerText;
    this.calendarBodyTemplate = calendarBodyTemplate;
    this.iterators = {
      left: shadowRoot.getElementById('iterate-left'),
      right: shadowRoot.getElementById('iterate-right'),
    };

    // contain all UI components within a single object
    this.uiComponents = {
      outputText: shadowRoot.getElementById('output-text'),
      outerContainer: shadowRoot.getElementById('outerContainer'),
      displayContainer: shadowRoot.getElementById('displayContainer'),
    };

    this.templateParser = new TemplateParser();

    // init stuff
    this.updateCurrentDateOutput();
    this.createCalendarBody();
    this.updateCalendarBody();
    this.updateOutputContainerText();
    this.initListeners();
    this.initRowListener();
    this.toggleOpen();
    console.log('testing');
  }

  /**
   * Listen for changes to the attrs defined in the static method observedAttributes
   * name of value changed is passed, as well as the oldValue and the new Value
   */
  attributeChangedCallback(name, oldValue, newValue) {
    // check the name and run your desired method
    if (name === 'open') {
      if (!this.hold) this.toggleOpen();
    } else if (name === 'disabled') {
      // TODO - change style on disabled
      return this.disabled ? this._setDisabled() : this._clearDisabled();
    } else if (name === 'selectedDate') {
    }
  }

  /**
   *
   * @return {void}
   */
  cacheDateObject() {
    this._cacheObject = {...this.dateObject};
  }

  /**
   *
   * @return {void}
   */
  cacheSelectedCell() {
    this._cacheSelectedCell = this.getSelectedCell();
  }

  /**
   *
   * @return {void}
   */
  rehydrateSelectedCell() {
    if (this._cacheSelectedCell === null) return;
    const cells = this.srPointer.querySelectorAll('[id="date-container"]');
    const filteredCells = Array.from(cells).filter((cell) => {
      const _cacheCellHTML = this._cacheSelectedCell.innerHTML;
      return cell.innerHTML === _cacheCellHTML;
    });

    if (filteredCells.length >= 0) this.setToSelected(filteredCells[0]);
  }

  /**
   *
   * @return {void}
   */
  rehydrateDateObjectFromCache() {
    this.dateObject = {...this._cacheObject};
  }

  /**
   *
   * @return {void}
   */
  getSelectedCell(className = 'selected') {
    const cells = this.srPointer.querySelectorAll('[id="date-container"]');
    const filteredCells = Array.from(cells).filter((cell) => {
      const cl = Array.from(cell.classList);
      return cl.indexOf(className) >= 0;
    });
    return filteredCells.length > 0 ? filteredCells[0] : null;
  }

  /**
   *
   * @return {void}
   */
  updateCurrentDateOutput() {
    this.dateObject.outputMonth = this.months()[
      Number(this.dateObject.month)
    ].name;
    this.dateObject.outputDOW = this.longformDowArray[this.dateObject.day];
  }

  /**
   *
   * @return {void}
   */
  createCalendarBody() {
    const div = this.calendarBodyTemplate.content.cloneNode(true);
    this.uiComponents.outerContainer.appendChild(div);
    this.uiComponents.calendarBody = this.srPointer.getElementById(
      'calendarContainer',
    );

    this.calendarBodyTemplateText = this.uiComponents.calendarBody.innerHTML;
  }

  initNav() {
    this.uiComponents.header = this.srPointer.getElementById('month-text');
    this.uiComponents.nav = {
      left: this.srPointer.getElementById('navLeft'),
      right: this.srPointer.getElementById('navRight'),
    };
    this.uiComponents.nav.left.addEventListener('mousedown', (e) => {
      if (e.target !== this.uiComponents.nav.left) return;
      this.dateObject.month--;
      if (this.dateObject.month < 1) {
        this.dateObject.month = 12;
        this.dateObject.year--;
      }
      this.updateCalendarBody();
      this.initRowListener();
    });
    this.uiComponents.nav.right.addEventListener('mousedown', (e) => {
      if (e.target !== this.uiComponents.nav.right) return;
      this.dateObject.month++;
      if (this.dateObject.month > 12) {
        this.dateObject.month = 1;
        this.dateObject.year++;
      }
      this.updateCalendarBody();
      this.initRowListener();
    });
    this.uiComponents.header.addEventListener('click', (e) => {
      if (e.target !== this.uiComponents.header) return;
      if (this.hold) return;
      this.open = !this.open;
      // const currViz = this.uiComponents.calendarBody.style.visibility;
      // const viz = currViz === 'hidden' ? 'visible' : 'hidden';
      // this.setCalendarVisibility(viz);
      const selectedCell = this.getSelectedCell();
      if (selectedCell === null) {
        this.rehydrateDateObjectFromCache();
      }

      this.updateCalendarDates();
      this.updateCurrentDateOutput();
      this.updateCalendarBody();
      this.rehydrateSelectedCell();
    });
    this.uiComponents.outerContainer.addEventListener('mouseleave', (e) => {
      // this.setCalendarVisibility('hidden');
    });
  }

  toggleOpen() {
    if (this.open) {
      this.setCalendarVisibility('visible');
      this.cacheDateObject();
      this.cacheSelectedCell();
    } else {
      this.setCalendarVisibility('hidden');
    }
  }

  setNewDate(date) {
    this.set('data-date', date);
    const selectedCell = Array.from(
      this.srPointer.querySelectorAll('.date-container'),
    ).filter((cell) => {
      return cell.getAttribute('data-date') === date;
    })[0];

    if (selectedCell === undefined) return;

    const formatMonth = Number(selectedCell.getAttribute('data-month'));
    const formatDay = Number(selectedCell.getAttribute('data-day'));
    this.dateObject.outputMonth =
      formatMonth > 12
        ? this.months()[1].name
        : this.months()[formatMonth].name;
    this.dateObject.date = formatDay;

    this.set('data-dow', selectedCell.getAttribute('data-dow'));

    this.updateOutputContainerText();
    this.setToSelected(selectedCell);
  }

  setCalendarVisibility(state) {
    this.uiComponents.calendarBody.style.visibility = state;
  }

  /**
   * Define all listener events
   *
   */
  initListeners() {
    // main ui component toggles visibility of the calendar body
    this.uiComponents.outerContainer.addEventListener('click', (e) => {
      if (e.target !== this.uiComponents.outerContainer) return;
      this.open = !this.open;
      // const currViz = this.uiComponents.calendarBody.style.visibility;
      // this.uiComponents.calendarBody.style.visibility = currViz === 'hidden' ? 'visible' : 'hidden';
    });

    this.iterators.left.addEventListener('mousedown', () => {
      if (this.disabled) return;
      this.iterateWeek(-1);
    });

    this.iterators.right.addEventListener('mousedown', () => {
      if (this.disabled) return;
      this.iterateWeek(1);
    });
  }

  /**
   * Iterate the calendar forward/back one wee
   * @param {int} num  [-1, 1]
   * @return {void}
   */
  iterateWeek(num) {
    // get all of the date-container cells
    const cells = this.srPointer.querySelectorAll('[id="date-container"]');

    // filter for the ONE cell that is 'today' or 'selected'.
    const currentDay = Array.from(cells).filter((day) => {
      const cl = Array.from(day.classList);
      return cl.indexOf('selected') >= 0;
      // return cl.indexOf('today') >= 0 || cl.indexOf('selected') >= 0;
    })[0];

    // get values of filtered cell
    let day = currentDay.getAttribute('data-day');
    let month = Number(currentDay.getAttribute('data-month'));
    let year = currentDay.getAttribute('data-year');

    // check days in current month
    let daysInCurrentMonth = this.months()[month].length;

    // update the day number by iterating up/down a value of 7
    let updatedDay = Number(day) + 7 * num;

    //
    if (updatedDay <= 0) {
      // cycle back one month
      month--;
      // if previous year
      if (month < 1) {
        // set month to Dec (11, [0-11 - for some reason])
        month = 12;
        // iterate the year back one
        year--;
      }
      // get the current number of days in the updated current month
      daysInCurrentMonth = this.months()[month].length;
      // updated day is the new number of monthly days + the negative number of days
      updatedDay = daysInCurrentMonth + updatedDay;

      // * if updated Day is greater than the days in the month, iterate the other way
    } else if (updatedDay > daysInCurrentMonth) {
      // start by getting the number of days into the month
      // ie: updatedDay = 33 & 30 days in month, we need to get to the 3rd of the next month
      updatedDay = updatedDay - daysInCurrentMonth;
      // iterate the month
      month++;
      // if changing years..
      if (month > 12) {
        // iterate the year
        year++;
        // reassign the month to 0
        month = 1;
      }
    }

    // format the month re: padded 0
    const formattedMonth = month < 10 ? `0${month}` : String(month);

    // may be redundant
    month = month < 10 ? `0${month}` : month;

    // todo - clean up?
    const iteratedDate = `${year}-${Number(month)}-${updatedDay}`;

    // update this.dateObject
    this.dateObject.month = Number(month);
    this.dateObject.date = updatedDay;
    this.dateObject.year = year;

    // run methods to update w/ udpated info, mostly redrawing
    this.setNewDate(iteratedDate);
    this.updateCurrentDateOutput();
    this.updateCalendarBody();
    this.initRowListener();

    this.updateOutputContainerText();

    // format the date to pass to getCellByUpdatedDate() method
    const formattedDate = updatedDay < 10 ? `0${updatedDay}` : updatedDay;
    this.getCellByUpdatedDate(formattedDate, formattedMonth, year);
  }

  /**
   * get the cell that needs to be 'selected' via the updated date, once filtered, call setToSelected()
   * if mode is set to 'weekly', call setWeeklyRange()
   *
   * @param {string} date
   * @param {string} month
   * @param {string} year
   * @return {void}
   */
  getCellByUpdatedDate(date, month, year) {
    // get celles
    const cells = this.srPointer.querySelectorAll('[id="date-container"]');

    // filter based on incoming date values
    const cellToSelect = Array.from(cells).filter((cell) => {
      return (
        cell.getAttribute('data-day') == date &&
        cell.getAttribute('data-month') == month &&
        cell.getAttribute('data-year') == year
      );
    })[0];

    // gatekeeper to bounce
    if (cellToSelect === null) return;

    // pass filtered cell to setToSelected method
    this.setToSelected(cellToSelect);

    // check the mode and then setWeeklyRange
    if (this.mode === 'weekly') {
      const row = cellToSelect.parentElement.parentElement;
      this.setWeeklyRange(row);
    }
  }

  /**
   *
   * @param {*} cell
   */
  setToSelected(cell) {
    this.clearSelected();
    cell.classList.add('selected');
  }

  /**
   *
   */
  clearSelected() {
    Array.from(this.srPointer.querySelectorAll('[id="date-container"]')).map(
      (cell) => {
        cell.classList.remove('selected');
      },
    );
  }

  /**
   * Define cell/row listeners
   *
   */
  initRowListener() {
    const rows = this.srPointer.querySelectorAll('[id="calRow"]');
    rows.forEach((row) => {
      const start_date = row.firstElementChild.firstElementChild.getAttribute(
        'data-date',
      );
      if (this.mode === 'weekly') {
        if (this.loadStatus) this.loadWeekStatus(start_date, row);
      }
      row.addEventListener('mousedown', (e) => {
        const target_date = e.target.getAttribute('data-date');
        const target_dow = e.target.getAttribute('data-dow');

        const day = Number(e.target.getAttribute('data-day'));
        const imonth = Number(e.target.getAttribute('data-month'));
        const year = e.target.getAttribute('data-year');
        //
        // TODO - need to account for different modes, "single", "range", "weekly", "range-triple"
        if (this.mode === 'weekly') {
          // TODO - need a better way to switch/case the modes on creation and init settings
          this.setWeeklyRange(row);

          if (this.activeDate) {
            this.dateObject.month = imonth;
            this.dateObject.date = day;
            this.dateObject.year = year;
            this.set('data-date', target_date);
            this.set('data-dow', target_dow);
            this.setToSelected(e.target);
          }
        } else if (this.mode === 'single') {
          //
          this.set('data-date', target_date);
          this.set('data-dow', target_dow);
          this.setToSelected(e.target);
        } else if (this.mode === 'range') {
          //
        } else if (this.mode === 'range-triple') {
          //
        }

        const month = target_date.split('-')[0];

        this.dateObject.outputMonth = month;
        this.dateObject.day = new Date(
          this.dateObject.year,
          this.dateObject.month,
          this.dateObject.date,
        ).getDay();

        if (this.viewport == 'day') {
          this.updateCurrentDateOutput();
          this.updateOutputContainerText();
        }
      });

      row.addEventListener('mouseup', (e) => {
        this.open = !this.open;
      });
    });
  }

  /**
   * sets data-to/data-from attributes for mode: weekly
   *
   * @param {*} row
   * @return void
   */
  setWeeklyRange(row) {
    const firstDayOfRange = row.firstElementChild.firstElementChild.getAttribute(
      'data-date',
    );
    const lastDayOfRange = row.lastElementChild.firstElementChild.getAttribute(
      'data-date',
    );
    this.set('data-from', firstDayOfRange);
    this.set('data-to', lastDayOfRange);
  }

  /**
   * setter method to pass attribute tag and value
   *
   * @param {*} dataAttr
   * @param {*} value
   */
  set(dataAttr, value) {
    this.setAttribute(dataAttr, value);
  }

  /**
   * Updates the date range showed to the user when component is in it's closed state
   *
   */
  updateOutputContainerText() {
    this.uiComponents.outputText.innerText = this.templateText(
      this.outputContainerTemplate,
      this.dateObject,
    );
  }

  /**
   * Update the month/year of
   *
   */
  updateCalendarBody() {
    this.updateCalendarHeader();
    this.updateCalendarDates();
    this.highlightCurrentDate();
    this.initNav();
  }

  /**
   *
   * @return {void}
   */
  highlightCurrentDate() {
    // get day, month, year
    const d = new Date().getDate();
    const m = new Date().getMonth() + 1;
    const y = new Date().getFullYear();

    // gather cells
    const cells = this.srPointer.querySelectorAll('[id="date-container"]');

    // filter cells based on day, month, year above
    const today = Array.from(cells).filter((cell) => {
      const cD = cell.getAttribute('data-day');
      const cM = cell.getAttribute('data-month');
      const cY = cell.getAttribute('data-year');
      return cD == d && cM == m && cY == y;
    });

    // if we get a cell returned (i.e., length > 0), add 'today' CSS class to classList
    if (today.length > 0) {
      today[0].classList.add('today');
      today[0].classList.add('selected');
    }
  }

  lowlightPreviousDays() {
    const cells = this.srPointer.querySelectorAll('[id="date-container"]');
    var len = cells.length;

    var today = false;
    for (var i = 0; i < len; i++) {
      const el = cells[i];
      const cl = Array.from(el.classList);
      if (cl.indexOf('today') >= 0) {
        today = true;
      }
      if (today) {
        break;
      }
      el.classList.add('past');
    }
  }
  /**
   * Update the month/year of the header
   *
   */
  updateCalendarHeader() {
    this.uiComponents.calendarBody.innerHTML = this.templateText(
      this.calendarBodyTemplateText,
      {
        month: this.months()[this.dateObject.month].name,
        year: this.dateObject.year,
      },
    );
  }

  /**
   *
   *
   */
  updateCalendarDates() {
    // get month/year from dateObject, don't adjust dateObject directly (yet)
    let currentMonth = this.dateObject.month;
    let currentYear = this.dateObject.year;

    // previous month accounting for year wrap
    const prevMonthKey = this.dateObject.month === 1 ? 12 : currentMonth - 1;
    const prevYearValue =
      prevMonthKey === 12 ? currentYear - 1 : this.dateObject.year;

    // next month accounting for year wrap
    const nextMonthKey = this.dateObject.month === 12 ? 1 : currentMonth + 1;
    const nextYearValue =
      nextMonthKey === 1 ? currentYear + 1 : this.dateObject.year;

    // set previous month
    const prevMonth = this.months()[Number(prevMonthKey)];
    prevMonth.monthNum = Number(prevMonthKey);

    // set current month
    const currMonth = this.months()[Number(this.dateObject.month)];
    currMonth.monthNum = Number(this.dateObject.month);

    // set next month
    const nextMonth = this.months()[Number(nextMonthKey)];
    nextMonth.monthNum = nextMonthKey;
    // get set vars re: month of days
    const dayOfFirstOfCurrMonth = currMonth.firstDayOfWeek;

    // define constants for readability
    // if first day of the month is > 5  & month.length >= 31
    // if month.length = 30 && first = 6
    // if month.length = 31 && first > 5

    let weeksInMonth = 5;

    if (currMonth.length === 30 && currMonth.firstDayOfWeek === 6) {
      weeksInMonth = 6;
    }

    if (currMonth.length === 31 && currMonth.firstDayOfWeek >= 5) {
      weeksInMonth = 6;
    }

    const daysInWeek = 7;

    // set 35 length date array (5 * 7), technically should be 6, since once or twice a year
    // the month fits into a 6 week scope, may or may not change the functionality for us..
    const dateArray = [];

    // remainder of prevMonth
    if (dayOfFirstOfCurrMonth > 0) {
      for (var ii = dayOfFirstOfCurrMonth; ii > 0; ii--) {
        dateArray.push({
          value: prevMonth.length - (ii - 1),
          month: prevMonth.name,
          monthNum: prevMonth.monthNum,
          active: false,
          year: prevYearValue,
        });
      }
    }

    // push all value of dates
    for (var jj = 1; jj <= currMonth.length; jj++) {
      //

      //  if (this.dateObject.month == todayMonth && this.dateObject.date == todayDay && this.dateObject.year == todayYear) {
      dateArray.push({
        value: jj,
        month: currMonth.name,
        monthNum:
          currMonth.monthNum < 10
            ? `0${currMonth.monthNum}`
            : currMonth.monthNum,
        year: this.dateObject.year,
      });
      //  } else {
      //     dateArray.push({
      //        value: jj,
      //        month: currMonth.name,
      //        monthNum: currMonth.monthNum < 10 ? `0${currMonth.monthNum}` : currMonth.monthNum,
      //        active: false,
      //        year: this.dateObject.year,
      //     });
      //  }
    }

    // remaining days of nextMonth to add to dateArray
    for (var x = 1; dateArray.length < weeksInMonth * daysInWeek; x++) {
      dateArray.push({
        value: x,
        month: nextMonth.name,
        monthNum: nextMonth.monthNum,
        active: false,
        year: nextYearValue,
      });
    }

    const dateContainers = this.srPointer.querySelectorAll(
      '[id^="date-container"]',
    );

    var p = 0;
    dateContainers.forEach((dC) => {
      const {value, month, monthNum, active, year} = dateArray[p];
      dC.innerText = value;
      const test = value < 10 ? `0${value}` : value;
      dC.setAttribute('data-month', monthNum);
      dC.setAttribute('data-year', year);
      dC.setAttribute('data-day', test);
      dC.setAttribute('data-date', `${year}-${monthNum}-${test}`);
      if (active) {
        dC.classList.add('today');
      }
      p++;
    });

    //
  }

  /**
   * Check if this.dateObject.year is a leap year
   *
   * @return bool
   */
  isLeapYear() {
    return this.dateObject.year % 4 === 0;
  }

  /**
   * Returns object with month names as keys and sub-props next level down
   * length, etc
   *
   * @return {Object}
   */
  months() {
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

  // ========================= CUSTOM   FUNCTIONS ========================= \\

  async loadWeekStatus(start_date, row) {
    const request = await fetch(
      `/schedule/statusbystartdate?date=${start_date}`,
    );
    await request.text().then((data) => {
      row.classList.add(data);
    });

    /**
     * new = green
     * open = lightgreen
     * commit = orange
     * pending = yellow
     * locked = lightred
     * date-locked = grey
     */
  }

  // ========================= TEMPLATE FUNCTIONS ========================= \\
  /**
   *
   * @param {string} string
   * @param {Object} data
   * @return {string}
   */
  templateText(string, data) {
    var tempStrHolder = this.replace(string, data);
    return this.loop(tempStrHolder, data);
  }

  /**
   *
   * @param {Object}
   * @return {Object}
   */
  regexMatchAll(regExp, string) {
    const matches = [];
    while (true) {
      const match = regExp.exec(string);
      if (match === null) break;
      matches.push(match);
    }
    return matches;
  }

  /**
   *
   */
  replace(string, object) {
    const keys = Object.keys(object);
    keys.forEach((key) => {
      // checks that key starts with a letter, to ignore array sets
      if (key.charAt(0).match(/[a-zA-Z]/)) {
        string = string.replace(new RegExp('{' + key + '}', 'gm'), object[key]);
      }
    });
    return string;
  }

  /**
   *
   */
  loop(string, object) {
    const pattern = /\[loop:begin\(([^]*?)\)\]([^]*?)\[loop:end\]/gm;
    let matches = [...this.regexMatchAll(pattern, string)];
    if (typeof matches !== 'undefined' && matches.length > 0) {
      matches = matches[0];
      const stringToReplace = matches[0];
      const array = matches[1];
      const stringToLoop = matches[2];
      let outputString = '';
      if (typeof object[array] !== 'undefined') {
        object[array].forEach((subArr) => {
          outputString += this.replace(stringToLoop, subArr);
        });
      } else {
        outputString = '';
      }
      return string.replace(stringToReplace, outputString);
    } else {
      return string;
    }
  }

  _setDisabled() {
    console.log('SET DISBALED');
    this.uiComponents.outerContainer.classList.add('oC-disabled');
  }
  _clearDisabled() {
    console.log('ClEAR DISBALED');
    this.uiComponents.outerContainer.classList.remove('oC-disabled');
  }
}

export default CalendarSelector;
