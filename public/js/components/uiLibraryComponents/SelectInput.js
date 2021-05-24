import CustomInput from './CustomInput.js';

var selectInputTemplate = document.createElement('template');

/**
 * Mobile Browser Override
 */
const modeOverride = window.innerWidth < 600;

// style obj
const style = {
  input: {
    border: `1px solid hsla(0, 0%, 50%, 1)`,
    borderRadius: 5,
    fontSize: modeOverride ? 18 : 16,
    height: modeOverride ? 50 : 36,
    paddingLeft: 14,
  },
  label: {
    fontSize: 16,
    color: `hsla(0, 0%, 25%, 1)`,
    fontWeight: 600,
    marginBottom: 8,
  },
  container: {
    height: modeOverride ? 86 : 76,
    marginBottom: modeOverride ? 12 : 8,
  },
};
selectInputTemplate.innerHTML = `
<style>
    :host {
        font-family: 'Montserrat', sans-serif;
        color: hsla(0, 0%, 25%, 1);
        width: fit-content;
        position: relative;
    }
    .select,
    input[type="password"], 
    input[type="text"] {
        border:  ${style.input.border};
        border-radius:  ${style.input.borderRadius}px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        color: hsla(0, 0%, 25%, 1);
        font-size: ${style.input.fontSize}px;
        height: ${style.input.height}px;
        padding-left:  ${style.input.paddingLeft}px;
        width: 100%;
        font-family: 'Montserrat', sans-serif;
        position: relative;
    }
   .select {
     padding-left: 0;
   }
    .select-preview {
      border-radius:  ${style.input.borderRadius}px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        color: hsla(0, 0%, 25%, 1);
        font-size: ${style.input.fontSize}px;
        width: 100%;
        height: 100%;
        font-family: 'Montserrat', sans-serif;
        position: relative;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        padding: 0 14px;
        user-select: none;
        white-space: nowrap;
        overflow: hidden;
        /* EXTRACT THESE */
        background-color: white; 
    }
    input[type="password"]:disabled, 
    input[type="text"]:disabled {
        background-color: hsla(0, 0%, 94%, 1);
    }
    .error-text {
        color: red;
        font-size: 12px;
        font-style: italic;
    }
    .container {
        display: flex;
        flex-direction: column;
        height: ${style.container.height}px;
        justify-content: flex-start;
        margin-bottom: ${style.container.marginBottom}px;
    }
    .container > div {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
    }
    .container-inline {
      margin-top: 33px;
      height: 55px;
    }
    .data-container {
      display: flex;
    }
    .data-container-inline {
    }
    .flex-col {
      flex-direction: column;
    }
    .flex-row {
      flex-direction: row;
    }
    .flex-start {
      justify-content: flex-start;
    }
    .error-container {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        margin-top: 5px;
        padding-right: 5px;
        width: 100%;
    }
    .data-container-inline > .label-container {
      margin: auto 15px auto 0
    }
    .flex-col {
      flex-direction: column;
    }
    .flex-row {
      flex-direction: row;
    }
    .flex-start {
      justify-content: flex-start;
    }
    .label-container {
        margin-bottom: ${style.label.marginBottom}px;
        font-size: ${style.label.fontSize}px;
        color: ${style.label.color};
        font-weight: ${style.label.fontWeight};
        width: fit-content;
        white-space: nowrap;
    }
    .input-container {
      width: 100%;
      min-width: 120px;
    }
    .option-selected {
      background-color: hsl(198, 20%, 88%);
    }

    .option-selected:before {
      content: '\\2713';
      position: absolute;
      top: 25%;
      left: 7px;
      font-size: 16px;
    }
    .select-dropdown {
      position: absolute;
      padding-inline-start: 0;
      padding-block-start: 0;
      list-style: none;
      margin-block-start: 0em;
      margin-block-end: 0em;
      box-sizing: border-box;
      -moz-box-sizing: border-box;
      -webkit-box-sizing: border-box;
      list-style-type: none;
      padding-inline-start: 0px;
      display: none;
      flex-direction: column;
      justify-content: flex-start;
      border:  ${style.input.border};
      border-radius:  ${style.input.borderRadius}px;
      box-sizing: border-box;
      -moz-box-sizing: border-box;
      -webkit-box-sizing: border-box;
      color: hsla(0, 0%, 25%, 1);
      font-size: ${style.input.fontSize}px;
      width: 100%;
      font-family: 'Montserrat', sans-serif;
      top: 100%;
      left: 0;
      overflow: hidden;
      max-height: 246px;
      overflow-y: auto;
      /* EXTRACT THESE */
      background-color: white; 
    }
    .select-dropdown > li {
      padding: 8px 12px 8px 24px;
      position: relative;
    }
    .select-dropdown > li:hover {
      background-color: hsl(198, 20%, 93%);
    }

    .select-dropdown  .disabled {
      color: hsla(0, 0%, 70%, 1);
    }
    .select-dropdown  .disabled:hover{
      background-color: white;
    }
    .open > .select-dropdown {
      display: flex;
      z-index: 5000000000;
    }
    .divider {
      height: 1px;
      margin: 9px 0;
      overflow: hidden;
      background-color: #e5e5e5;
      padding: 0;
    }
    .option-selected, .option-selected:hover {
      background-color: hsl(198, 20%, 88%) !important;
    }
    .select-preview > .toggle {
      font-size: 14px;
      color: hsla(0, 0%, 30%, 1);
      pointer-events: none;
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      margin: auto 0px;
      padding: 0 8px;
      background-color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .toggle > span {
      transform: rotate(90deg);
    }
    .select-preview-text {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
</style>
<div class="container" id="container">
    <!-- Main container: holds the input and label -->
    <div class="data-container flex-start" id="data-container">
      <div class="label-container">
       <label for="input">{label}</label>
      </div>
      <div class="input-container">
        <div class="select" id="input"> 
          <div class="select-preview">
            <span class="select-preview-text" id="select-preview-container"></span>
            <span class="toggle"><span>&#10148;</span></span>
          </div>
          <ul class="select-dropdown" id="select-child-container"></ul>
        </div>
      </div>
    </div>
    <!-- Error container: holds the error message and gives margin-bottom -->
    <div class="error-container">
        <span id="error-text" class="error-text"></span>
    </div>
</div>`;

/**
 * mode: inline {- -}, block { = }
 *
 */

class SelectInput extends CustomInput {
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
    const hasVal = Boolean(val);
    if (hasVal) {
      this.setAttribute('value', val);
    } else {
      this.removeAttribute('value');
    }
  }

  //

  constructor() {
    super();
    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({mode: 'open'});

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(selectInputTemplate.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot;

    //  this.init();

    this.options = [];
  }

  connectedCallback() {
    this.init();

    this.extendCaptures();

    this.swapOptions();

    this.initPrimeSelected();

    this.initSelectListener();
  }

  extendCaptures() {
    this.childContainer = this.srPointer.getElementById(
      'select-child-container',
    );
    this.previewContainer = this.srPointer.getElementById(
      'select-preview-container',
    );
  }

  mouseOutContainer(e) {
    if (!this.open) return;
    var event = e.toElement || e.relatedTarget;
    if (event.parentNode == this || e == this) return;
    if (this.open) {
      this.open = !this.open;
    }
  }

  /**
   * Grabs elements from light dom and inserts them into the input option
   */
  swapOptions() {
    const options = Array.from(this.children);

    console.log('options', options);
    if (options.length <= 0) return;
    options.forEach(node => {
      // get values from each imported node
      const newLiObj = {
        value: node.value,
        innerText: node.innerText,
        disabled: node.disabled,
        selected: node.selected,
      };

      const newOption = this.createNewLi(newLiObj);
      // create and append LI elements from the captured data
      this.childContainer.appendChild(newOption);

      this.options.push(newOption);
      // - Remove the nodes from the light dom
      node.remove();
    });

    //
    this.previewContainer.innerText = options[0].innerText;
  }

  createNewLi(obj) {
    const li = document.createElement('li');
    li.innerText = obj.innerText;
    li.classList.add('select-element');
    li.setAttribute('data-value', obj.value);

    if (obj.disabled) {
      li.classList.add('disabled');
      li.setAttribute('data-disabled', '');
    }
    if (obj.selected) {
      li.classList.add('option-selected');
      li.setAttribute('data-selected', '');
    }
    return li;
  }

  initPrimeSelected() {
    const selected = this.options.filter(node => {
      return node.hasAttribute('data-selected');
    });

    if (selected.length > 1) {
      alert(
        'Too many elements marked selected. Must only be one for single selector element.',
      );
    }

    if (selected.length <= 0) return;
    this.setSelectValue(selected[0]);
    this.setOptionState(selected[0]);
    this.setPreviewText(selected[0]);
  }

  initSelectListener() {
    this.input.addEventListener('mouseleave', e => this.mouseOutContainer(e), {
      passive: true,
    });

    this.input.addEventListener('click', e => {
      if (!this.multi) {
        this.open = !this.open;
      } else {
        if (e.target === this.previewContainer) {
          this.open = !this.open;
        }
      }

      // return if not proper element, in this case LI
      if (!this.isElementLi(e.target)) return;
      if (!e.target.getAttribute('data-value')) return;
      this.setSelectValue(e.target);
      this.setOptionState(e.target);
      this.setPreviewText(e.target);
    });
  }

  isElementLi(el) {
    if (typeof el === 'undefined') return;

    return el.tagName === 'LI';
  }
  setPreviewText(el) {
    if (typeof el === 'undefined') return;

    this.previewContainer.innerText =
      this.value === null ? this.options[0].innerText : el.innerText;
  }

  setOptionState(el) {
    if (!this.multi) {
      this.resetAllOptionStates();
    }
    if (this.isSelected(el)) {
      this.markUnselected(el);
    } else {
      this.markSelected(el);
    }
  }
  resetAllOptionStates() {
    this.options.forEach(opt => {
      this.markUnselected(opt);
    });
  }
  markSelected(el) {
    if (typeof el === 'undefined') return;
    el.classList.add('option-selected');
  }

  markUnselected(el) {
    if (typeof el === 'undefined') return;
    el.classList.remove('option-selected');
  }

  isSelected(el) {
    if (typeof el === 'undefined') return;
    const cl = Array.from(el.classList);
    return cl.indexOf('option-selected') >= 0 && el.value !== null;
  }

  setSelectValue(el) {
    if (typeof el === 'undefined') return;
    this.value = el.getAttribute('data-value');
    if (this.internals_) {
      this.internals_.setFormValue(this.value);
    }
  }

  setValue(value) {
    const selected = this.options.filter(node => {
      return node.getAttribute('data-value') == this.value;
    })[0];
    this.setOptionState(selected);
    this.setPreviewText(selected);
  }

  toggleOpen() {
    if (this.open) {
      this.input.classList.add('open');
    } else {
      this.input.classList.remove('open');
    }
  }
  setZIndex() {}

  injectValue(data) {
    data.forEach(value => {
      const newLiObj = {
        value: value.id || value.value,
        innerText: value.innerText || value.value,
        disabled: value.disabled,
        selected: value.selected,
      };

      const newOption = this.createNewLi(newLiObj);
      // create and append LI elements from the captured data
      this.childContainer.appendChild(newOption);

      this.options.push(newOption);
    });

    this.setSelectValue(this.options[0]);
    this.setOptionState(this.options[0]);
    this.setPreviewText(this.options[0]);
    // this.value = this.options[0].value;
    // this.setValue();
  }
}

export default SelectInput;

/**
 * Things to do to expand functionality:
 *    1.) Event listener for alphanumeric keypress to auto scroll/auto select elements
 */
