import {trio} from './componentProps.js';
/**
 * instatiate the template element
 */
let trioSelector = document.createElement('template');

/**
 * Set innerHTML of the template element. This element will get appended to the shadow root
 */
trioSelector.innerHTML = `
<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
<style>
:host {
   margin: 5px;
}
.container {
    width: fit-content;
    display: flex;
    flex-direction: column;
    justify-content: left;
    margin: 5px 10px;
    font-family: 'Roboto', sans-serif;
}
.label-container { 
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    margin-bottom: 5px;
    padding-bottom: ${trio.label.paddingBottom}px;
    padding-left: ${trio.label.paddingLeft}px;
    padding-right: ${trio.label.paddingRight}px;
    padding-top: ${trio.label.paddingTop}px;
}
.label-text {
    font-size: ${trio.label.fontSize}px;
    font-weight: ${trio.label.fontWeight};
    text-align: left;
    color: ${trio.label.fontColor};
    text-transform: capitalize;
    letter-spacing: ${trio.label.letterSpacing}px;
    font-family: 'Montserrat', sans-serif;

}
.slider-container {
    margin-left: ${trio.sliderContainer.marginLeft}px;
    background-color: ${trio.sliderContainer.background};
    border-radius: ${trio.sliderContainer.borderRadius}px;
    display: flex;
    flex-direction: row;
    height: ${trio.sliderContainer.height}px;
    justify-content: space-between;
    position: relative;
    width: ${trio.sliderContainer.width}px;
    z-index: 0;
}
.data-node {
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    z-index: 2;
}
.data-text {
    color: ${trio.valueText.fontColor};
    text-transform: ${trio.valueText.textTransform};
    font-weight: ${trio.valueText.fontWeight};
    letter-spacing: ${trio.valueText.letterSpacing}px;
    font-size: ${trio.valueText.fontSize}px;
    user-select: none;
    font-family: 'Montserrat', sans-serif;

}
.slider {
    background-color: ${trio.slider.background};
    border-radius: ${trio.slider.borderRadius}px;
    height: ${trio.slider.height}px;
    left: 0;
    margin: 2px;
    position: absolute;
    top: 0;
    width: ${trio.slider.width}px;
    z-index: 1;
    transition: all ${trio.animation.speed}s ease-out;
}
.slider-step-0 {
    left: 0%;
}
.slider-step-1 {
    left: 33.5%;
}
.slider-step-2 {
    left: 67%;
}

.disabled > .container {
   opacity: .8;
 }
 .disabled > .slider-container > .slider {
   background-color: hsla(0, 0%, 93%, 1);
 }  
 
 .disabled > .slider-container > .data-node > .data-text {
   color: hsla(0, 0%, 70%, 1);
 }
</style>
<div class="container" id="container">
    <div class="label-container">
        <span class="label-text">{label}</span>
    </div>
    <div class="slider-container" id="slider-container">
        <div class="data-node" id="data-node" data-index="0">
            <span class="data-text">{left}</span>
        </div>
        <div class="data-node" id="data-node" data-index="1">
            <span class="data-text">{middle}</span>
        </div>
        <div class="data-node" id="data-node" data-index="2">
            <span class="data-text">{right}</span>
        </div>
        <div class="slider" id="slider"></div>
    </div>
</div>
`;

export class TrioSelect extends HTMLElement {
   static get observedAttributes() {
      return ['value', 'disabled'];
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
    * <trio-select label="Test Label" left="Yes" middle="maybe" right="No"></trio-select>
    * method name cooresponds to object property, i.e., this.label for get label(){}
    * Some also match the bracketed templates within the innerHTML string above, so we can
    * regex and replace
    */
   get label() {
      return this.hasAttribute('label') ? this.getAttribute('label') : 'Please enter a label attribute. [label="Example Label"]';
   }
   get left() {
      return this.hasAttribute('left') ? this.getAttribute('left') : 'false';
   }
   get right() {
      return this.hasAttribute('right') ? this.getAttribute('right') : 'true';
   }
   get middle() {
      return this.hasAttribute('middle') ? this.getAttribute('middle') : 'middle';
   }

   /**
    * getter and setter for state 'value' and 'disabled'.
    * Rather than being treated like a checkbox, this element has 3 states, 0, 1, 2 defined
    * by the data-index attribute in the data-node objects in above template string
    */
   get value() {
      // return a default value of 0 if value not set by default
      return this.hasAttribute('value') ? this.getAttribute('value') : 0;
   }

   set value(val) {
      if (val) {
         this.setAttribute('value', val);
      } else {
         this.removeAttribute('value');
      }
   }

   get disabled() {
      return this.hasAttribute('disabled');
   }
   set disabled(val) {
      const isDiabled = Boolean(val);
      if (val) {
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

      this.internals_ = this.attachInternals();

      // create and attach the shadow dom
      const shadowRoot = this.attachShadow({mode: 'open'});

      // append the template element above w/ cloneNode
      shadowRoot.appendChild(trioSelector.content.cloneNode(true));

      /**
       *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
       *  */
      this.srPointer = shadowRoot;

      // get main container element so we can template the values on load
      this.mainContainer = shadowRoot.getElementById('container');
      this.templateValues();

      // get slider container and the slider element to deal with animation and click events
      this.sliderContainer = shadowRoot.getElementById('slider-container');
      this.slider = shadowRoot.getElementById('slider');

      // init the listener items
      this.initListeners();

      // set slider position based on default value
      this.toggleSliderPosition();
   }

   /**
    * Listen for changes to the attrs defined in the static method observedAttributes
    * name of value changed is passed, as well as the oldValue and the new Value
    */
   attributeChangedCallback(name, oldValue, newValue) {
      // check the name and run your desired method
      if (name === 'value') {
         this.toggleSliderPosition(this.value);
      } else if (name === 'disabled') {
         // TODO - change style on disabled
         this.toggleDisabled();
      }
   }

   /**
    * init the listeners
    */
   initListeners() {
      // bail if disabled
      if (this.disabled) return;

      /**
       * Since this element is index activated rather than a simple boolean,
       * we need to grab the nodes that as targets, loop and set the event listener
       */
      const nodes = this.shadowRoot.querySelectorAll('#data-node');
      nodes.forEach(node => {
         node.addEventListener('click', e => {
            const index = node.getAttribute('data-index');
            this.value = index; // setting this.value calls attributeChangeCallback
            this.internals_.setFormValue(this.value);
         });
      });
   }

   /**
    * capture the values and then replace them within the innerHTML of the main container
    */
   templateValues() {
      // create an object w/ the props that were caught by the earlier getters
      const caughtValues = {
         label: this.label,
         left: this.left,
         right: this.right,
         middle: this.middle,
      };

      // replace mainContainer html w/ the templated string.
      this.mainContainer.innerHTML = this.replace(this.mainContainer.innerHTML, caughtValues);
   }

   /**
    * replace bracket templates within a string w/ matching keys from an object
    */
   replace(string, object) {
      const keys = Object.keys(object);
      keys.forEach(key => {
         // checks that key starts with a letter, to ignore array sets and numbers
         // ? I intended to flesh this out more, however a typeof check would be more concise
         if (key.charAt(0).match(/[a-zA-Z]/)) {
            string = string.replace(new RegExp('{' + key + '}', 'gm'), object[key]);
         }
      });
      return string;
   }

   toggleDisabled() {
      if (this.disabled) {
         this.mainContainer.classList.add('disabled');
      } else {
         this.mainContainer.classList.remove('disabled');
      }
   }

   /**
    * toggle the class list of this.slider to set the animation
    */
   toggleSliderPosition() {
      if (this.value === undefined) this.value = 0;
      this.addSliderStepClass();
   }

   /**
    * remove any present 'slider-step' classes from the #slider element
    */
   removeSliderStepClasses() {
      // get the classlist as an array
      const cl = Array.from(this.slider.classList);

      // filter any array elements that return an index of 'slider-step'
      const presentClasses = cl.filter(c => {
         return c.indexOf('slider-step') >= 0;
      });

      // loop through the filtered array and remove any elements that exist from the classList
      presentClasses.forEach(pC => {
         this.slider.classList.remove(pC);
      });
   }

   /**
    * calls removeSliderStepClasses and then inserts the new one based on updated this.value
    */
   addSliderStepClass() {
      this.removeSliderStepClasses();
      this.slider.classList.add(`slider-step-${this.value}`);
   }
}

// define the tag ofthe custom element
export default TrioSelect;
