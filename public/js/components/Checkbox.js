import { check } from './componentProps.js';

/**
 * Slider element
 *
 * Attributes:
 * label, left, right, background, labelPosition
 *
 * 1.) label: displayed text
 * 2.) left: value on left, defaults to 'false'
 * 3.) right: value on right, defaults to 'true'
 * 4.) background: allows user to changed the active background color. Accepts
 *       hsl, hsla, rgb, hex, and css defined colors
 * 5.) labelPosition: positions label ['left', 'right']. Defaults to 'right'
 */

/**
 * instatiate the template element
 */
let customCkbxTemplate = document.createElement('template');

/**
 * Set innerHTML of the template element. This element will get appended to the shadow root via cloneNode()
 */
customCkbxTemplate.innerHTML = `
<style>
:host {
   width: fit-content;
   margin: 5px;
}
.container {
   width: 100%;
   display: flex;
   flex-direction: row;
   justify-content: flex-start;
   margin: 10px 10px;
}
.label-container {
   display: flex;
   flex-direction: column;
   justify-content: center;
   margin-bottom: 0px;
   padding-bottom: ${check.label.paddingBottom}px;
   padding-left: ${check.label.paddingLeft}px;
   padding-right: ${check.label.paddingRight}px;
   padding-top: ${check.label.paddingTop}px;
   margin-left: ${check.labelContainer.marginLeft}px;
}
.label-text {
   font-family: "Times New Roman", Times, serif; 
   font-size: ${check.label.fontSize}px;
   font-weight: ${check.label.fontWeight};
   text-align: left;
   color: ${check.label.fontColor};
   text-transform: capitalize;
   letter-spacing: ${check.label.letterSpacing}px;
   margin-left: ${check.label.marginLeft}px;
   font-family: 'Montserrat', sans-serif;

}
.slider-container {
  margin: auto 0;
   margin-left: ${check.sliderContainer.marginLeft}px;
   background-color: ${check.sliderContainer.background};
   border-radius: ${check.sliderContainer.borderRadius}px;
   display: flex;
   flex-direction: row;
   height: ${check.sliderContainer.height}px;
   justify-content: space-between;
   position: relative;
   width: ${check.sliderContainer.width}px;
   z-index: 0;
   transition: background-color .2s ease-out;
}
.data-node {
    ont-family: "Times New Roman", Times, serif; 
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    z-index: 2;
}
.data-text {
    color: ${check.valueText.fontColor};
    text-transform: ${check.valueText.textTransform};
    font-weight: ${check.valueText.fontWeight};
    letter-spacing: ${check.valueText.letterSpacing}px;
    font-size: ${check.valueText.fontSize}px;
    user-select: none;

}
.slider {
    background-color: ${check.slider.background};
    border-radius: ${check.slider.borderRadius}%;
    height: ${check.slider.height}px;
    left: 0;
    margin: 3px;
    position: absolute;
    top: 0;
    width: ${check.slider.width}px;
    z-index: 1;
    transition: all ${check.animation.speed}s ease-out;
}
.slider-step {
  left: 40%
}
.slider-container-active {
    background-color: ${check.slider.activeBackground};
}
</style>

<div class="container" id="container"> 
    <div class="slider-container" id="slider-container">
        <div class="slider" id="slider"></div>
    </div>
    <div class="label-container" id="label-container">
        <span class="label-text" id="text-container">{label}</span>
    </div>
</div>
`;

export class uiCheck extends HTMLElement {
  static get observedAttributes() {
    return ['checked', 'disabled', 'label'];
  }

  static get formAssociated() {
    return true;
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
   * following methods retrieve attribute values from the HTML element, in this case
   * <ui-check label="Test Label" left="Yes" middle="maybe" right="No"></ui-check>
   * method name cooresponds to object property, i.e., this.label for get label(){}
   * Some also match the bracketed templates within the innerHTML string above, so we can
   * regex and replace
   */
  get label() {
    return this.getAttribute('label') || 'Please enter a label attribute. [label="Example Label"] If no label desired, enter an empty one character string, i.e., " " ';
  }
  set label(val) {
    const hasVal = Boolean(val);
    if (hasVal) {
      this.setAttribute('label', val);
    } else {
      this.removeAttribute('label');
    }
  }

  get background() {
    return this.getAttribute('background');
  }

  get labelPosition() {
    return this.getAttribute('labelPosition') || 'right';
  }

  get size() {
    return this.getAttribute('size') || 'regular';
  }
  /**
   * getter and setter for state 'checked' and 'disabled'.
   * Rather than being treated like a checkbox, this element has 3 states, 0, 1, 2 defined
   * by the data-index attribute in the data-node objects in above template string
   */
  get checked() {
    return this.hasAttribute('checked');
  }

  set checked(val) {
    const isChecked = Boolean(val);
    if (isChecked) {
      this.setAttribute('checked', '');
    } else {
      this.removeAttribute('checked');
    }
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

  /**
   * constructor method
   */
  constructor() {
    super(); // must call super() first

    //
    if (typeof this.attachInternals === 'undefined') {
    } else {
      this.internals_ = this.attachInternals();
    }

    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({ mode: 'open' });

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(customCkbxTemplate.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot;

    // get main container element so we can template the values on load
    this.mainContainer = shadowRoot.getElementById('container');
    this.templateValues();

    // get slider container and the slider element to deal with animation and click events
    this.labelContainer = shadowRoot.getElementById('label-container');
    this.sliderContainer = shadowRoot.getElementById('slider-container');
    this.textContainer = shadowRoot.getElementById('text-container');
    this.formatLabelPosition();

    this.slider = shadowRoot.getElementById('slider');

    //
    // this.checked = false;

    // init the listener items
    this.initListeners();

    // set slider position based on default value
    this.toggleSliderPosition();

    this.updateBackgroundColor();

    this.updateSize();
  }

  connectedCallback() {
    this.value = this.checked;
    if (this.internals_) {
      this.internals_.setFormValue(this.checked);
    }
  }
  /**
   * Run methods to change the size of the element
   */
  updateSize() {
    this.updateElementSize();
    this.updateTextSize();
  }

  /**
   * Adjust size of the container element and slider
   */
  updateElementSize() {
    //
    if (this.size != 'regular') {
      //
      this.updateSliderContainerSize();
      this.updateSliderSize();
      this.updateMargins();
      this.updateMainContainerSize();
    }
  }

  updateSliderContainerSize() {
    this.sliderContainer.style.height = `20px`;
    this.sliderContainer.style.width = `34px`;
  }

  updateSliderSize() {
    this.slider.style.height = `16px`;
    this.slider.style.width = `16px`;
    this.slider.style.margin = `2px`;
  }

  updateMargins() {
    if (this.labelPosition === 'left') {
      this.sliderContainer.style.marginLeft = `15px`;
    } else {
      this.labelContainer.style.marginLeft = `15px`;
    }
  }

  updateMainContainerSize() {
    this.mainContainer.style.margin = `2px`;
  }
  /**
   * Adjust size of the text of the label
   */
  updateTextSize() {
    //
    if (this.size != 'regular') {
      //
      this.textContainer.style.fontSize = `14px`;
    }
  }

  /**
   * Changes the positioning of the label
   */
  formatLabelPosition() {
    if (this.labelPosition === 'left') {
      const clone = this.sliderContainer.cloneNode(true);
      this.mainContainer.appendChild(clone);
      this.sliderContainer.remove();
      this.sliderContainer = clone;

      this.applyAttributes(this.labelContainer, check._leftPos_label);
      this.applyAttributes(this.sliderContainer, check._leftPos_sliderContainer);
    }
  }

  /**
   * Apply background attribute to element. if null, the method is immediately returned. Otherwise,
   * it checks this.checked and applies color accordingly. Uncheck value is pulled from formatting obj
   */
  updateBackgroundColor() {
    if (!this.background) return;
    if (this.checked) {
      this.sliderContainer.style.backgroundColor = this.background;
    } else {
      this.sliderContainer.style.backgroundColor = check.sliderContainer.background;
    }
  }
  /**
   * Listen for changes to the attrs defined in the static method observedAttributes
   * name of value changed is passed, as well as the oldValue and the new Value
   */
  attributeChangedCallback(name, oldValue, newValue) {
    // check the name and run your desired method
    if (name === 'checked') {
      this.toggleSliderPosition(this.checked);
    } else if (name === 'label') {
      this.textContainer.innerText = this.label;
    } else {
      // TODO - change style on disabled
    }
  }

  /**
   * init the listeners
   */
  initListeners() {
    this.sliderContainer.addEventListener('click', (e) => {
      if (this.disabled) return;
      this.checked = !this.checked;
      this.value = this.checked;
      if (this.internals_) {
        this.internals_.setFormValue(this.checked);
      }
    });
  }

  /**
   * capture the values and then replace them within the innerHTML of the main container
   */
  templateValues() {
    // create an object w/ the props that were caught by the earlier getters
    const caughtValues = {
      label: this.label,
    };

    // replace mainContainer html w/ the templated string.
    this.mainContainer.innerHTML = this.replace(this.mainContainer.innerHTML, caughtValues);
  }

  /**
   * replace bracket templates within a string w/ matching keys from an object
   */
  replace(string, object) {
    const keys = Object.keys(object);
    keys.forEach((key) => {
      // checks that key starts with a letter, to ignore array sets and numbers
      // ? I intended to flesh this out more, however a typeof check would be more concise
      if (key.charAt(0).match(/[a-zA-Z]/)) {
        string = string.replace(new RegExp('{' + key + '}', 'gm'), object[key]);
      }
    });
    return string;
  }

  /**
   * toggle the class list of this.slider to set the animation
   */
  toggleSliderPosition() {
    if (this.checked) {
      this.sliderContainer.classList.add('slider-container-active');
      this.slider.classList.add('slider-step');
    } else {
      this.sliderContainer.classList.remove('slider-container-active');
      this.slider.classList.remove('slider-step');
    }
    this.updateBackgroundColor();
  }

  /**
   * Apply attributes to an element from an object of js-css
   */
  applyAttributes(element, obj) {
    const keys = Object.keys(obj);
    keys.forEach((key) => {
      element.style[key] = obj[key];
    });
  }
}

// define the tag ofthe custom element
// customElements.define('ui-checkbox', uiCheck);
export default uiCheck;
