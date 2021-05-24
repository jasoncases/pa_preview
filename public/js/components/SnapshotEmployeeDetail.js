import TemplateParser from './class/TemplateParser.js';
import CoreModule from '../CoreModule.js';

var snapshotEmployeeDetailTemplate = document.createElement('template');

snapshotEmployeeDetailTemplate.innerHTML = `
<style>
    :host {
        font-family: 'Montserrat', sans-serif;
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
    .container {
        width: 100%;
    }
    .snap-row {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        padding: 8px;
        font-size: 16px;
        min-height: fit-content;
        color: black;
     }
     .snap-row-item {
        height: auto;
        pointer-events: none;
     }
     .snap-status {
        overflow: hidden;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        border: 1px solid black;
        border-radius: 3px;
        padding: 0;
     }
     .snap-status-item {
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-transform: capitalize;
        padding: 3px;
     }
     .snap-status-item > span {
         padding-left: 8px;
     }
     @keyframes pulse {
        0% {
           opacity: 1;
           filter: brightness(125%);
        }
        50% {
           opacity: 0.6;
           filter: brightness(70%);
        }
        100% {
           opacity: 1;
           filter: brightness(125%);
        }
     }
     .snap-badge {
        height: 22px;
        width: 15px;
        align-self: flex-end;
        margin: 3px 5px;
     }
     .lunch-border {
        border: 2px solid yellow;
        background-color: hsl(120, 50%, 35%);
        border-radius: 2px;
     }
     .break-border {
        border: 2px solid orange;
        background-color: hsl(120, 50%, 35%);
        border-radius: 2px;
     }
     .lunch {
        background-color: yellow;
     }
     .break {
        background-color: orange;
     }
     .snap-label {
        font-size: 1.2rem;
        color: black;
     }
     .snap-input {
        font-size: 1.2rem !important;
        height: fit-content !important;
     }
     .snap-active {
        background-color: #00cc33;
        color: black;
     }
     .snap-break {
        background-color: orange;
     }
     .snap-lunch {
        background-color: yellow;
     }
     .snap-pulse {
        animation: pulse 0.5s;
        animation-iteration-count: infinite;
     }
     .snap-row-sub-row {
        justify-content: space-between;
        color: black;
        text-transform: uppercase;
     }
     .snap-row-sub-row:first-child {
        border-bottom: 1px solid darkgray;
     }
     .snap-row-sub-row:nth-child(even) {
        background-color: rgb(192, 192, 192);
     }
     .snap-row-sub-row > span {
     }
     .snap-row-sub-item {
        justify-content: center;
        padding-left: 8px;
     }
     .snap-edit-details {
         display: none;
         padding-left: 8px;
      flex-direction: column;
     }
     .open > .snap-edit-details {
         display: flex;
     }
     .flex-1 {
         flex: 1;
     }
     .snap-row-alerts {
         display: flex;
         flex-direction: row;
         justify-content: space-between;
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
      
      .btn-transparent {
        background-color: transparent;
        background-repeat: no-repeat;
        border: none;
        overflow: hidden;
        outline: none;
      }
      
      .btn-small {
        padding: 4px 4px;
        /* line-height: 2rem; */
        font-size: 1.2rem;
      }
      .text-dark {
          color: hsla(0, 0%, 30%, 1);
      }
</style>

<div class="container" id="container">
    <div class="snap-row" id="top-row">
        <div class="snap-row-item flex-1">
            <div class="snap-status {active}">
                <div class="snap-status-item">
                    <span>{name}</span>
                </div>
                <div class="snap-status-item">
                    <div class="snap-row-alerts">
                        <div class="snap-badge break-border {break1} {break1-active}"></div>
                        <div class="snap-badge lunch-border {lunch} {lunch-active}"></div>
                        <div class="snap-badge break-border {break2} {break2-active}"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="snap-row snap-edit-details">
        <div class="flex-row flex-center">Activity Details</div>
        [loop:begin(actions)]
        <div class="snap-row-sub-row flex-row" id="row-{id}">
            
                <div class="flex-col flex-1 snap-row-sub-item" style="font-size:1.2rem;text-align: left;">
                    {text}
                </div>
                <div class="flex-col flex-1 snap-row-sub-item" style="font-size:1.2rem;text-align:right;padding-right:4px;" id="time-{id}">
                    {time}
                </div>
                <div class="flex-col snap-row-sub-item">
                    <button
                        type="button"
                        class="btn btn-transparent btn-small text-dark"
                        data-employeeId="{empid}"
                        data-stampId="{id}"
                        id="edit"
                        data-timesheetId="{id}"
                        data-text="{text}"
                        data-year="{year}"
                        data-day="{day}"
                        data-month="{month}"
                        data-ss="{ss}"
                        data-mm="{mm}"
                        data-hh="{hh}"
                    >
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                </div>
        </div>
        [loop:end]
    </div>
</div>
`;

class SnapshotEmployeeDetail extends HTMLElement {
  static get observedAttributes() {
    return ['open', 'state', 'disabled'];
  }

  get open() {
    return this.hasAttribute('open');
  }

  set open(val) {
    if (val) {
      this.setAttribute('open', true);
    } else {
      this.removeAttribute('open');
    }
  }
  get state() {
    return JSON.parse(this.getAttribute('state')) || null;
  }

  set state(val) {
    const hasState = Boolean(val);
    if (hasState) {
      this.setAttribute('state', val);
    } else {
      this.removeAttribute('state');
    }
  }

  constructor() {
    super();

    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({ mode: 'open' });

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(snapshotEmployeeDetailTemplate.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot;
  }

  connectedCallback() {
    //
    this.init();
  }
  attributeChangedCallback(name, oldValue, newValue) {
    //
    console.log(`${name} has changed...`);
    if (name === 'open') {
      this.toggleOpen();
    } else if (name === 'state') {
      if (oldValue == newValue) return;
      if (this.open) {
        this.suspendUpdate();
      } else {
        this.updateState();
      }
    }
  }

  init() {
    this.injectFontAwesome();
    this.mainContainer = this.srPointer.getElementById('container');

    this.initMainContainerTemplate();
    this.initTemplater(new TemplateParser());
    this.initCoreModule(new CoreModule());
    this.templateValues();
    this.runAfterTemplating();
  }

  injectFontAwesome() {
    const link = document.createElement('link');
    link.setAttribute('rel', 'stylesheet');
    link.setAttribute('href', 'https://pro.fontawesome.com/releases/v5.8.1/css/all.css');
    link.setAttribute('integry', 'sha384-Bx4pytHkyTDy3aJKjGkGoHPt3tvv6zlwwjc3iqN7ktaiEMLDPqLSZYts2OjKcBx1');
    link.setAttribute('crossorigin', 'anonymous');
    this.srPointer.appendChild(link);
  }
  initCoreModule(CoreModule) {
    this.CoreModule = CoreModule;
  }

  initTemplater(TemplateParser) {
    this.TemplateParser = TemplateParser;
  }

  initMainContainerTemplate() {
    this.template = this.mainContainer.innerHTML;
  }

  templateValues() {
    console.log('teompateValues called');
    if (!this.state) return;
    console.log('teompateValues called STEP 2');
    this.mainContainer.innerHTML = this.TemplateParser.templateText(this.template, this.state);
  }

  runAfterTemplating() {
    this.initTargetElements();
    this.initListeners();
  }

  initTargetElements() {
    this.topRow = this.srPointer.getElementById('top-row');
    this.captureEditButtons();
  }
  initListeners() {
    this.topRow.addEventListener('click', (e) => {
      this.open = !this.open;
    });

    this.initEditButtonListeners();
  }

  captureEditButtons() {
    this.editButtons = this.srPointer.querySelectorAll('button');
  }

  initEditButtonListeners() {
    this.editButtons.forEach((btn) => {
      btn.addEventListener('click', (e) => {
        const row = btn.parentElement;
        console.log('row:', row);
        console.log(btn.dataset);
      });
    });
  }
  updateState() {
    console.log('update state called....');
    this.templateValues();
    this.runAfterTemplating();
  }

  suspendUpdate() {
    console.log('Suspend called.....');
    if (this.open) {
      setTimeout(() => {
        this.suspendUpdate();
      }, 2500);
    } else {
      console.log("it passed and we're updating!");
      this.updateState();
    }
  }
  toggleOpen() {
    if (this.open) {
      this.mainContainer.classList.add('open');
    } else {
      this.mainContainer.classList.remove('open');
    }
  }
}

export default SnapshotEmployeeDetail;
