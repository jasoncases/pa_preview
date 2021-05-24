var template = document.createElement('template');

template.innerHTML = `
<style>
    :host {
        /* */
        width: fit-content;
        
    }
    div {
      box-sizing: border-box;
      -moz-box-sizing: border-box;
      -webkit-box-sizing: border-box;
    }
    .container {
        width: 219px;
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        font-weight: 600;
        font-size: 14px;
        font-family: 'Montserrat', sans-serif;
        border-radius: 5px;
        background-color: hsl(210, 75%, 70%);
        border: 1px solid hsl(210, 55%, 40%);
        color: hsl(210, 55%, 15%);
        user-select: none;
        position: relative;
      }
      .displayContainer {
        width: 100%;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        padding: 4px;
      }

      .displayNode {
        height: 100%;
        width: fit-content;
        margin: 0 2px;
      }
      .displayIcon {
        margin-left: 8px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
      }
      .displayNodeContainer {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        padding: 4px;
        background-color: hsl(210, 75%, 80%);
        border: 1px solid hsl(210, 55%, 40%);
        border-radius: 5px;
      }
      .displayNodeContainer:hover {
        filter: brightness(110%);
        cursor: pointer; 
      }
</style>

<!-- -->
<div class="container" id="mainContainer">
  <datetime-date id="ui:calendar"></datetime-date>
  <datetime-time id="ui:time"></datetime-time>
  <div class="displayContainer" id="displayContainer">
    <div class="displayNodeContainer" id="ui:calendarToggle">
      <div class="displayNode" id="ui:dateDisplay">03/03/2003</div>
      <div class="displayIcon"><i class="far fa-calendar"></i></div>
    </div>
    <div class="displayNodeContainer" id="ui:timeToggle">
      <div class="displayNode" id="ui:timeDisplay">16:45</div>
      <div class="displayIcon"><i class="far fa-clock"></i></div>
    </div>
  </div>
</div>
`;

class DateTime extends HTMLElement {
  static get observedAttributes() {
    return ['value', 'disabled', 'date', 'time'];
  }

  get mode() {
    return this.getAttribute('mode') || 'datetime';
  }

  get date() {
    return this.getAttribute('date') || '[]';
  }

  set date(val) {
    const hasValue = Boolean(val);
    if (hasValue) {
      this.setAttribute('date', val);
    } else {
      this.removeAttribute('date');
    }
  }

  get time() {
    return this.getAttribute('time');
  }

  set time(val) {
    const hasValue = Boolean(val);
    if (hasValue) {
      this.setAttribute('time', val);
    } else {
      this.removeAttribute('time');
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

  constructor() {
    super();
    this.value = '{}';
    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({ mode: 'open' });

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(template.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot;
  }

  connectedCallback() {
    this.injectFontAwesome();
    this._init();
  }

  attributeChangedCallback(name, oldValue, newValue) {
    if (name === 'date') {
      this._updateDateDisplay();
      this._updateValue();
    } else if (name === 'time') {
      this._updateTimeDisplay();
      this._updateValue();
    }
  }

  _updateValue() {
    const tmpValue = JSON.parse(this.value);
    tmpValue.date = JSON.parse(this.date);
    tmpValue.time = JSON.parse(this.time);
    this.value = JSON.stringify(tmpValue);
  }
  _updateTimeDisplay() {
    const time = JSON.parse(this.time);
    this._timeDisplay.innerText = `${time.hour}:${time.minutes}`;
  }

  _updateDateDisplay() {
    let display;
    const date = JSON.parse(this.date);
    if (date.length < 1) {
      display = 'n/a';
    } else if (date.length == 1) {
      display = date[0];
    } else {
      display = 'Multiple';
    }
    this._setDateDisplay(display);
  }
  _init() {
    this._registerAll();
    this._initListeners();
  }

  _registerAll() {
    this._registerCalendarToggle();
    this._registerTimeToggle();
    this._registerCalendarElement();
    this._registerTimeElement();
    this._registerDateDisplay();
    this._registerTimeDisplay();
  }

  _registerTimeDisplay() {
    this._timeDisplay = this.srPointer.getElementById('ui:timeDisplay');
  }
  _registerDateDisplay() {
    this._dateDisplay = this.srPointer.getElementById('ui:dateDisplay');
  }

  _registerCalendarElement() {
    this._calendarElement = this.srPointer.getElementById('ui:calendar');
    this._calendarElement.DateTime = this;
  }
  _registerTimeElement() {
    this._timeElement = this.srPointer.getElementById('ui:time');
    this._timeElement.DateTime = this;
  }

  _registerCalendarToggle() {
    this._calendarToggle = this.srPointer.getElementById('ui:calendarToggle');
  }

  _registerTimeToggle() {
    this._timeToggle = this.srPointer.getElementById('ui:timeToggle');
  }

  _initListeners() {
    this._calendarToggle.addEventListener('click', (event) => this._mouseClickCalendar(event));
    this._timeToggle.addEventListener('click', (event) => this._mouseClickTime(event));
  }

  _mouseClickCalendar(event) {
    this._calendarElement.open = true;
  }

  _setDateDisplay(val) {
    this._dateDisplay.innerText = val;
  }

  _mouseClickTime(event) {
    console.log('event:', event);
    this._timeElement.open = true;
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

export default DateTime;
