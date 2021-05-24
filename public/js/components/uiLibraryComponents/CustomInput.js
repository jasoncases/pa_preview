import TemplateParser from '../class/TemplateParser.js';
import Err from './Err/Err.js';

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
    height: modeOverride ? 86 : 82,
    marginBottom: modeOverride ? 12 : 8,
  },
};

/**
 * Input Styled Component Library Element
 *
 * mode = 'span', 'small', 'medium'
 * orientation = 'column', 'row'
 */
class CustomInput extends HTMLElement {
  static get observedAttributes() {
    return [`disabled`, `err`, `value`, `orientation`, `open`];
  }

  static get formAssociated() {
    return true;
  }

  get mode() {
    const allowed = ['small', 'medium', 'span'];
    const mode = this.getAttribute('mode');

    // check override and return span if on mobile
    if (modeOverride) return 'span';

    return allowed.indexOf(mode) >= 0 ? mode : 'medium';
  }

  get required() {
    return this.hasAttribute('required');
  }

  get disabled() {
    return this.hasAttribute('disabled');
  }
  set disabled(val) {
    const isDisabled = Boolean(val);
    if (isDisabled) {
      this.setAttribute('disabled', '');
    } else {
      this.removeAttribute('disabled');
    }
  }

  get err() {
    return this.getAttribute('err');
  }
  set err(val) {
    const isErr = Boolean(val);
    if (isErr) {
      this.setAttribute('err', val);
    } else {
      this.removeAttribute('err');
    }
  }

  get placeholder() {
    return this.getAttribute('placeholder') || '';
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

  get orientation() {
    const allowed = ['inline', 'block', null];
    if (allowed.indexOf(this.getAttribute('orientation')) < 0) {
      alert(`orientation attribute incorrect.`);
    }
    return this.getAttribute('orientation') || 'block';
  }
  set orientation(val) {
    const isSet = Boolean(val);
    if (isSet) {
      this.setAttribute('orientation', val);
    } else {
      this.removeAttribute('orientation');
    }
  }
  get label() {
    return this.getAttribute('label') || 'No label found. A label attribute must be declared.';
  }

  /**
   * Form controls
   */
  // The following properties and methods aren't strictly required,
  // but native form controls provide them. Providing them helps
  // ensure consistency with native controls.
  get form() {
    return this.internals_.form;
  }
  get name() {
    return this.getAttribute('name');
  }
  get type() {
    return this.localName;
  }
  get validity() {
    return this.internals_.validity;
  }
  get validationMessage() {
    return this.internals_.validationMessage;
  }
  get willValidate() {
    return this.internals_.willValidate;
  }

  checkValidity() {
    return this.internals_.checkValidity();
  }
  reportValidity() {
    return this.internals_.reportValidity();
  }

  /**
   * constructor method
   */
  constructor() {
    super(); // must call super() first

    if (typeof this.attachInternals === 'function') {
      this.internals_ = this.attachInternals();
    }

    //
    this.state = null;
  }

  hasInternals() {
    return typeof this.attachInternals;
  }
  /** *****************************************************************************************
   * Capture Methods
   * **************************************************************************************** */
  initElements() {
    this.labelContainer = this.srPointer.getElementById('label-container');
    this.textContainer = this.srPointer.getElementById('input-container');
    this.input = this.srPointer.getElementById('input');
    this.errorContainer = this.srPointer.getElementById('error-container');
    this.errorText = this.srPointer.getElementById('error-text');
    this.dataContainer = this.srPointer.getElementById('data-container');
  }

  /** *****************************************************************************************
   * Set Methods -
   *  * Use these methods to set some states, handle attr changes, etc
   *  * setMode adjusts the width of the container depending on value of mode attr
   * **************************************************************************************** */

  /**
   * Method that sets mode values, primarily container width
   */
  setMode() {
    this.mainContainer.style.width = this._config.mode.width[this.mode];
  }

  /**
   *
   */
  setErr() {
    if (!this.state) {
      setTimeout(() => {
        this.setErr();
      }, 200);
      return;
    }
    this.errorText.innerText = Err[this.err];
  }

  clearErr() {
    this.err = false;
    this.errorText.innerText = '';
  }
  /**
   * Insertion method. Necessary because we dont want to use value getter/setter for security on
   * on the PassInput extension.
   *
   * this prevents the input text to be visible as plain text
   */
  setValue(value = null) {
    console.log('value:', value);
    if (!this.state) {
      setTimeout(() => {
        this.setValue(value);
      }, 200);
      return;
    }
    if (this.disabled) return;
    this.value = value;
    this.input.value = value;
    this.internals_.setFormValue(this.value);
  }

  setDisabled() {
    if (!this.input) {
      setTimeout(() => {
        this.setDisabled();
      }, 200);
      return;
    }
    this.input.disabled = true;
  }

  setOrientation() {
    if (!this.dataContainer) return;
    if (this.orientation === 'inline') {
      this.dataContainer.classList.add('flex-row');
      this.dataContainer.classList.add('data-container-inline');
      this.mainContainer.classList.add('container-inline');
    } else {
      this.dataContainer.classList.add('flex-col');
    }
  }
  /** *****************************************************************************************
   * Listener Methods
   * **************************************************************************************** */

  initListeners() {
    this.input.addEventListener('keyup', (e) => {
      this.value = this.input.value;
      if (this.pass) {
        this.value = btoa(this.value);
      }

      if (this.internals_) {
        this.internals_.setFormValue(this.value);
      }
    });
  }
  /** *****************************************************************************************
   * Init, i.e., Run-At-Load Methods
   * **************************************************************************************** */

  init() {
    // get main container element so we can template the values on load
    this.mainContainer = this.srPointer.getElementById('container');

    // get slider container and the slider element to deal with animation and click events

    this._config = {
      mode: {
        width: {
          small: `fit-content`,
          medium: `500px`,
          span: `100%`,
        },
      },
    };

    // run main init fuctions
    this.template(new TemplateParser()); // set templater
    this.templateInitialValues(); // template incoming balues
    this.initElements(); // init all elements (MUST come after templating)
    this.setMode(); // change any mode related styling
    this.initListeners(); // init any default listeners
    this.setOrientation(); // change orientation related styling

    // set state to initialized
    this.state = 'initialized';
  }
  /** *****************************************************************************************
   * CustomHTML Component Lifecycle Methods
   * **************************************************************************************** */
  connectedCallback() {
    //
    this.init();
  }
  /**
   * Listen for changes to the attrs defined in the static method observedAttributes
   * name of value changed is passed, as well as the oldValue and the new Value
   */
  attributeChangedCallback(name, oldValue, newValue) {
    // check the name and run your desired method
    if (name === 'err') {
      // TODO - change style on disabled
      this.setErr();
    } else if (name === 'disabled') {
      this.setDisabled();
    } else if (name === 'orientation') {
      this.setOrientation();
    } else if (name === 'open') {
      this.toggleOpen();
    } else if (name === 'value') {
      if (oldValue === newValue) return;
      this.setValue(newValue);
    }
  }

  /** *****************************************************************************************
   *  Template initialization methods
   * **************************************************************************************** */

  /**
   * Set top level templater
   * @param {class} TemplateParser
   */
  template(TemplateParser) {
    this.TemplateParser = TemplateParser;
  }

  /**
   * Run the templating methods, replacing the maincontainer text w/ {templates} with the same text with inserted values
   */
  templateInitialValues() {
    const valuesToTemplate = {
      label: this.label,
      placeholder: this.placeholder,
    };
    this.mainContainer.innerHTML = this.TemplateParser.templateText(this.mainContainer.innerHTML, valuesToTemplate);
  }
}

export default CustomInput;
