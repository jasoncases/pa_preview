var template = document.createElement('template');

template.innerHTML = `
<style>
    :host {
        position: relative;
        
        z-index: 1000000;
    }
    div {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
      }
     
    .container {
        width: 219px;
        display: none;
        flex-direction: row;
        justify-content: space-between;
        font-weight: 600;
        font-size: 14px;
        font-family: 'Montserrat', sans-serif;
        border-radius: 5px;
        background-color: hsl(210, 75%, 70%);
        background-color: hsl(210, 55%, 40%);
        border: 1px solid hsl(210, 55%, 40%);
        color: hsl(210, 55%, 15%);
        user-select: none;
        position: absolute;
        top: -1px;
        left: -1px;
        padding: 4px;
    }
    .open {
        display: flex;
    }
    .timeCell {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        padding: 3px;
        background-color: hsl(210, 75%, 70%);
        border: 1px solid hsl(210, 55%, 40%);
        border-radius: 5px;
    }
    .timeCell > label {
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin-right: 4px;
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
<div class="container" id="ui:mainContainer">
    <div class="calendarCloser" id="closer"><i class="fas fa-times-circle"></i></div>
    <div class="timeCell">
        <label for="hour">Hour:</label>
        <select class="timeSelect hour" name="hour" id="ui:hours">
            <option value="00">00</option>
            <option value="01">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
        </select>
    </div>
    <div class="timeCell">
        <label for="minutes">Minutes:</label>
        <select class="timeSelect hour" name="minutes" id="ui:minutes">
            <option id="00">00</option>
            <option id="05">05</option>
            <option id="10">10</option>
            <option id="15">15</option>
            <option id="20">20</option>
            <option id="25">25</option>
            <option id="30">30</option>
            <option id="35">35</option>
            <option id="40">40</option>
            <option id="45">45</option>
            <option id="50">50</option>
            <option id="55">55</option>          
        </select>
    </div>
</div>
`;

class DateTimeTime extends HTMLElement {
  static get observedAttributes() {
    return ['open', 'value', 'disabled'];
  }

  get mode() {
    return this.getAttribute('mode') || 'child';
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
    this.value = '{}';

    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({ mode: 'open' });

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(template.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot;

    this.timeObject = {
      hour: this.padStart(new Date().getHours()),
      minute: this.padStart(Math.ceil(new Date().getMinutes() / 5) * 5),
    };

    this._nowCache = { ...this.timeObject };
  }

  padStart(value) {
    return value < 10 ? `0${value}` : `${value}`;
  }

  connectedCallback() {
    this._init();
  }

  _init() {
    this.injectFontAwesome();
    this._registerAll();
    this._initListeners();
    this._setDefaultValue();
  }

  _setDefaultValue() {
    this._setHourValue(this.timeObject.hour);
    this._setMinuteValue(this.timeObject.minute);
    this._setValue();
    this._setInitOpenValue();
    this._setCloserVisibleStatus();
    this._setContainerPosition();
  }

  _setInitOpenValue() {
    if (this.mode === 'child') {
      this.open = false;
    } else {
      this.open = true;
    }
  }

  _setContainerPosition() {
    if (this.mode !== 'child') {
      this._mainContainer.style.position = 'relative';
    }
  }
  _setCloserVisibleStatus() {
    if (this.mode !== 'child') {
      this._closer.remove();
    }
  }
  _setHourValue(value) {
    this._hour.value = value;
  }
  _setMinuteValue(value) {
    this._minutes.value = value;
  }
  _getHourValue() {
    return this._hour.value;
  }
  _getMinuteValue() {
    return this._minutes.value;
  }
  _setValue() {
    const tmpValue = JSON.parse(this.value);
    tmpValue.hour = this._getHourValue();
    tmpValue.minutes = this._getMinuteValue();
    this.value = JSON.stringify(tmpValue);
  }
  _setParentValue() {
    if (this.mode !== 'child') return;
    this.DateTime.time = this.value;
  }
  _registerAll() {
    this._registerMainContainer();
    this._registerHourElement();
    this._registerMinuteElement();
    this._registerCloser();
  }

  _registerMainContainer() {
    this._mainContainer = this.srPointer.getElementById('ui:mainContainer');
  }
  _registerCloser() {
    this._closer = this.srPointer.getElementById('closer');
  }

  _registerHourElement() {
    this._hour = this.srPointer.getElementById('ui:hours');
  }

  _registerMinuteElement() {
    this._minutes = this.srPointer.getElementById('ui:minutes');
  }

  _initListeners() {
    this._hourListeners();
    this._minuteListeners();
    this._closerListeners();
  }

  _hourListeners() {
    this._hour.addEventListener('change', (event) => this._hourChange(event));
  }
  _minuteListeners() {
    this._minutes.addEventListener('change', (event) => this._minutesChange(event));
  }

  _hourChange(event) {
    this._setValue();
  }
  _minutesChange(event) {
    this._setValue();
  }
  _closerListeners() {
    this._closer.addEventListener('click', (event) => this._closerClick(event));
  }

  toggleOpen() {
    if (this.open) {
      this._mainContainer.classList.add('open');
    } else {
      this._mainContainer.classList.remove('open');
    }
  }
  _closerClick(event) {
    this.open = !this.open;
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
      this._updateValueDisplay();
      this._setParentValue();
    }
  }

  _updateValueDisplay() {
    this._hour.value = JSON.parse(this.value).hour;
    this._minutes.value = JSON.parse(this.value).minutes;
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

export default DateTimeTime;
