import TextInput from './TextInput.js';

/**
 * PassInput
 *
 * Value is defaul exported with btoa(value), so once caught,
 * it needs to be decoded via atob() in JS or base64_decode in PHP
 */
class PassInput extends TextInput {
  constructor() {
    super(); // must call super() first

    this.pass = true;
  }

  /** *****************************************************************************************
   * Set Methods -
   *  * Use these methods to set some states, handle attr changes, etc
   *  * setType changes the input type from text to password
   * **************************************************************************************** */
  setType() {
    this.input.type = 'password';
  }

  setValue(val) {
    console.log('incoming val:', `'${val}'`);
    // this.value = val;
    console.log('set value called');
    this.value = val;
    this.input.value = val;
  }
  /** *****************************************************************************************
   * CustomHTML Component Lifecycle Methods
   * **************************************************************************************** */
  connectedCallback() {
    //   this.updateType();
    this.init();

    // change type to password
    this.setType();

    this.createShowIcon();
    this.appendShowIcon();
    this.initShowIconListener();
  }
  /**
   * Listen for changes to the attrs defined in the static method observedAttributes
   * name of value changed is passed, as well as the oldValue and the new Value
   */
  attributeChangedCallback(name, oldValue, newValue) {
    // check the name and run your desired method
    if (name === 'checked') {
      this.toggleSliderPosition(this.checked);
    } else if (name === 'err') {
      // TODO - change style on disabled
      this.setErr();
    } else if (name === 'value') {
      //  this.setValue();
    }
  }

  createShowIcon() {
    const span = document.createElement('span');
    span.setAttribute(
      'style',
      'position:absolute;right:0;top:0;font-size:14px;color:gray;user-select:none;cursor:pointer;height:100%;display:flex;flex-direction:column;justify-content:center;text-align:center;margin-right:9px;font-weight:600;',
    );
    span.innerText = 'Show';

    this.showIcon = span;

    return span;
  }

  appendShowIcon() {
    const target = this.input.parentNode;
    target.appendChild(this.showIcon);
  }

  initShowIconListener() {
    this.showIcon.addEventListener('click', (e) => {
      const currentInputType = this.input.type;
      this.input.type = currentInputType === 'password' ? 'text' : 'password';
      this.showIcon.innerText = currentInputType === 'password' ? 'Hide' : 'Show';
    });
    this.showIcon.addEventListener('mouseenter', (e) => {
      this.showIcon.setAttribute(
        'style',
        'position:absolute;right:0;top:0;font-size:14px;color:hsl(210, 65%, 40%);user-select:none;cursor:pointer;height:100%;display:flex;flex-direction:column;justify-content:center;text-align:center;margin-right:9px;font-weight:600;',
      );
    });
    this.showIcon.addEventListener('mouseleave', (e) => {
      this.showIcon.setAttribute(
        'style',
        'position:absolute;right:0;top:0;font-size:14px;color:gray;user-select:none;cursor:pointer;height:100%;display:flex;flex-direction:column;justify-content:center;text-align:center;margin-right:9px;font-weight:600;',
      );
    });
  }
}

export default PassInput;
