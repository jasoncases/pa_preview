import TemplateParser from '../class/TemplateParser.js';
import CoreModule from '../../CoreModule.js';
import { hour24options, minutesSeconds, months, daysOfMonth, currentYear1Buffer } from '../Snippets/Select.date.js';
import { Action } from '../../PayrollModule/src/Components/Actions/Actions.js';
import { Response } from '../../System/Components/Response/Response.js';

var snapshotEdit = document.createElement('template');

snapshotEdit.innerHTML = `
<style>
    :host {
        font-family: 'Montserrat', sans-serif;
        user-select: none;
        margin: 0 auto;
    }

    .btn-action {
        background-color: hsl(147, 57%, 38%);
        color: white;
      }
      
      /* *--------------------------------------  ACTION [MODIFIERS] ------- */
      .btn-action:hover {
        background-color: #3eb976;
      }
    .btn-danger {
        background-color: rgb(216, 61, 47);
        color: white;
      }
      
      /* !--------------------------------------  DANGER [MODIFIERS] ------- */
      
      .btn-danger:hover {
        background-color: rgb(221, 98, 87);
        color: white;
      }
      .btn-span {
        width: 100%;
        padding: 8px;
        margin: 8px 0;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
      }
    div {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
    }
    .flex-col {
        display: flex;
        flex-direction: column;
    }
    .flex-row {
        display: flex;
        flex-direction: row;
    }
    .flex-center {
        justify-content: center;
    }
    .flex-evenly {
        justify-content: space-evenly;
    }
    .container {
        margin: 20px;
        background-color: hsla(49, 98%, 83%, 1);
        justify-content: flex-start;
        border: 1px solid black;
        position: relative;
    }
    
     .btn {
        -webkit-appearance: none;
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
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        user-select: none;
        /* z-index: 1000; */
      }
      .container > .flex-row {
        background-color: lightgray;
    }
      .hidden {
         display: none !important;
      }
      .title {
          font-weight: 600;
          font-size: 16px;
      }
      .title > span {
          margin: 4px auto;
      }
      .header {
          font-weight: 500;
          font-size: 14px;
          background-color: hsla(0, 0%, 70%, 1) !important;
      }
      .cell {
          flex: 1;
          display: flex;
          flex-direction: row;
          justify-content: center;
          margin: 8px auto;
      }
      .cell select {
        font-size: 12px;
      }
      .btn-container {
          padding: 15px 8px;
          display: flex;
          flex-direction: row;
          justify-content: space-between;
      }
     
      .state-pending > .flex-row {
          filter: blur(2px);
      }





    /* BEGIN: SPINNER CSS */

      .spinner-container {
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-content: center;
          position: absolute;
          top: 50%;
          margin-top: -32px;
          left: 50%;
          margin-left:  -32px;
      }

    .lds-spinner {
    color: official;
    display: inline-block;
    position: relative;
    width: 64px;
    height: 64px;
    }
    .lds-spinner div {
    transform-origin: 32px 32px;
    animation: lds-spinner 1.2s linear infinite;
    }
    .lds-spinner div:after {
    content: " ";
    display: block;
    position: absolute;
    top: 3px;
    left: 29px;
    width: 5px;
    height: 14px;
    border-radius: 20%;
    background: rgb(22, 60, 78);
    }
    .lds-spinner div:nth-child(1) {
    transform: rotate(0deg);
    animation-delay: -1.1s;
    }
    .lds-spinner div:nth-child(2) {
    transform: rotate(30deg);
    animation-delay: -1s;
    }
    .lds-spinner div:nth-child(3) {
    transform: rotate(60deg);
    animation-delay: -0.9s;
    }
    .lds-spinner div:nth-child(4) {
    transform: rotate(90deg);
    animation-delay: -0.8s;
    }
    .lds-spinner div:nth-child(5) {
    transform: rotate(120deg);
    animation-delay: -0.7s;
    }
    .lds-spinner div:nth-child(6) {
    transform: rotate(150deg);
    animation-delay: -0.6s;
    }
    .lds-spinner div:nth-child(7) {
    transform: rotate(180deg);
    animation-delay: -0.5s;
    }
    .lds-spinner div:nth-child(8) {
    transform: rotate(210deg);
    animation-delay: -0.4s;
    }
    .lds-spinner div:nth-child(9) {
    transform: rotate(240deg);
    animation-delay: -0.3s;
    }
    .lds-spinner div:nth-child(10) {
    transform: rotate(270deg);
    animation-delay: -0.2s;
    }
    .lds-spinner div:nth-child(11) {
    transform: rotate(300deg);
    animation-delay: -0.1s;
    }
    .lds-spinner div:nth-child(12) {
    transform: rotate(330deg);
    animation-delay: 0s;
    }
    @keyframes lds-spinner {
    0% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
    }
    /* END: SPINNER CSS */
    </style>

    <div class="container flex-col" id="container">
        <!-- HEADER ROW -->
        <div class="flex-row title">
            <span>{text}</span>
        </div>
        <div class="flex-row header">
            <div class="cell">
                <span>Hour</span>
            </div> 
            <div class="cell">
                <span>Min</span>
            </div>
            <div class="cell">
                <span>Secs</span>
            </div>
        </div>
        <div class="flex-row">
            <div class="cell">
                <span>
                    <select id="hh">
                        ${hour24options}
                    </select>
                </span>
            </div>
            <div class="cell">
                <span>
                    <select id="mm">
                        ${minutesSeconds}
                    </select>
                </span>
            </div>
            <div class="cell">
                <span>
                    <select id="ss">
                        ${minutesSeconds}
                    </select>
                </span>
            </div>
        </div>
        <div class="flex-row header">
            <div class="cell">
                <span>Month</span>
            </div>
            <div class="cell">
                <span>Day</span>
            </div>
            <div class="cell">
                <span>Year</span>
            </div>
        </div>
        <div class="flex-row">
            <div class="cell">
                <span>
                    <select id="month">
                        ${months}
                    </select>
                </span>
            </div>
            <div class="cell">
                <span>
                    <select id="day">
                        ${daysOfMonth}
                    </select>
                </span>
            </div>
            <div class="cell">
                <span>
                    <select id="year">
                        ${currentYear1Buffer}
                    </select>
                </span>
            </div>
        </div>
        <!-- Y:Mo:D ENTER ROW -->
        <!-- BUTTON ROW -->
        <div class="flex-row btn-container">
            <input type="hidden" id="timesheetId" value="{timesheetid}" />
            <input type="hidden" id="shiftId" value="{shiftid}" />
            <button type="button" class="btn btn-span btn-danger" id="reset">Reset</button>
            <button type="submit" class="btn btn-span btn-action" id="submit">Submit</button>
            </form>
        </div> 
        <!-- END BUTTON ROW -->
    </div>
`;

// TODO - THIS IS TEMPORARY! I MEAN IT!
/**
 * Putting this in here temporarily to get me a loading state. Need to finish fleshing out the LoaderElement component
 */
const loadState = `
  <div class="lds-spinner">
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
  </div>
`;

class SnapshotEdit extends HTMLElement {
  static get observedAttributes() {
    return ['state', 'disabled'];
  }

  get state() {
    return JSON.parse(this.getAttribute('state'));
  }

  set state(val) {
    const hasState = Boolean(val);
    if (hasState) {
      this.setAttribute('state', JSON.stringify(val));
    } else {
      this.removeAttribute('state');
    }
  }
  constructor() {
    super();

    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({ mode: 'open' });

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(snapshotEdit.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */

    this.srPointer = shadowRoot;
    this.mainContainer = this.srPointer.getElementById('container');

    this._cache = {
      template: this.mainContainer.innerHTML,
    };
  }

  connectedCallback() {
    this.init();
  }

  attributeChangedCallback(name, oldValue, newValue) {
    if (name === 'state') {
    }
  }

  init() {
    this.initTemplater(new TemplateParser());
    this.initCoreModule(new CoreModule());
    this.templateValues();
    this.initTargetElements();
    this.initListeners();
    this.setDefaults();
    this.cacheIncomingTimestamp();
  }

  templateValues() {
    this.mainContainer.innerHTML = this.TemplateParser.templateText(this._cache.template, this.state);
  }

  initTargetElements() {
    this.reset = this.srPointer.getElementById('reset');
    this.submit = this.srPointer.getElementById('submit');
    this.hh = this.srPointer.getElementById('hh');
    this.ss = this.srPointer.getElementById('ss');
    this.mm = this.srPointer.getElementById('mm');
    this.month = this.srPointer.getElementById('month');
    this.day = this.srPointer.getElementById('day');
    this.year = this.srPointer.getElementById('year');
    this.timesheetId = this.srPointer.getElementById('timesheetId');
    this.shiftId = this.srPointer.getElementById('shiftId');
  }

  cacheIncomingTimestamp() {
    this._cache.timeStamp = this.buildTimestampString();
  }

  setDefaults() {
    const keys = Object.keys(this.state);
    keys.forEach((key) => {
      if (this[key]) {
        this[key].value = this.state[key];
      }
    });
  }
  initListeners() {
    this.reset.addEventListener('click', (e) => {
      this.removeSelfOnSuccess();
    });
    this.submit.addEventListener('click', (e) => this.mouseClickContainer(e));
  }
  initTemplater(TemplateParser) {
    this.TemplateParser = TemplateParser;
  }
  initCoreModule(CoreModule) {
    this.Core = CoreModule;
  }
  initLoader() {}
  mouseClickContainer(event) {
    this.updateTime();
  }

  /**
   *
   */
  async updateTime() {
    const obj = this.buildTimestampString();

    // compare cache to new
    if (obj === this._cache.timeStamp) {
      return;
    }

    // if the cache and new are not equal, blur the container and append the loader
    this.appendLoaderElement();
    this.blurContainer();

    //   console.log('this.timesheetId: ', this.timesheetId.value);
    //   console.log('this.shiftId: ', this.shiftId.value);
    //   console.log('this._cache.timeStamp:', this._cache.timeStamp);
    //   console.log('obj:', obj);

    Action.updateStamp(parseInt(this.shiftId.value), parseInt(this.timesheetId.value), obj, this._cache.timeStamp).then((response) => {
      console.log('update: ', response);
      if (response.status == 'success') {
        this.remove();
        Response.get();
      } else {
        Response.put('danger', 'Error updating timestamp.');
      }
    });
  }

  /**
   *
   */
  blurContainer() {
    this.mainContainer.classList.add('state-pending');
  }

  unblurContainer() {
    this.mainContainer.classList.remove('state-pending');
  }
  /**
   *
   */
  buildTimestampString() {
    const t = {
      hh: this.hh.value,
      mm: this.mm.value,
      ss: this.ss.value,
      month: this.month.value,
      day: this.day.value,
      year: this.year.value,
    };
    return `${t.year}-${t.month}-${t.day} ${t.hh}:${t.mm}:${t.ss}`;
  }

  /** **************************************************************
   *
   * Loader Element Methods
   *
   * ************************************************************* */
  createLoaderElement() {
    const div = document.createElement('div');
    div.id = 'loader';
    div.classList.add('spinner-container');
    div.innerHTML = loadState;
    this.loader = div;
    return div;
  }

  appendLoaderElement() {
    this.mainContainer.appendChild(this.createLoaderElement());
  }

  removeLoaderElement() {
    if (this.loader) {
      this.loader.remove();
      this.loader = null;
    }
  }

  /**
   * Run on server 200 response
   */
  removeSelfOnSuccess() {
    this.remove();
    this.parent.showDetailContainer();
  }
}

export default SnapshotEdit;
