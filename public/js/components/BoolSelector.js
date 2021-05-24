import {bool} from './componentProps.js';
/**
 * instatiate the template element
 */
let boolSelector = document.createElement('template');

/**
 * Set innerHTML of the template element. This element will get appended to the shadow root
 */
boolSelector.innerHTML = `
<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">

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
   }
.label-container {
    display: flex;  
    flex-direction: row;
    justify-content: flex-start;
    margin-bottom: 5px;
    padding-bottom: ${bool.label.paddingBottom}px;
    padding-left: ${bool.label.paddingLeft}px;
    padding-right: ${bool.label.paddingRight}px;
    padding-top: ${bool.label.paddingTop}px;
}
.label-text {
    font-size: ${bool.label.fontSize}px;
    font-weight: ${bool.label.fontWeight};
    text-align: left;
    color: ${bool.label.fontColor};
    text-transform: capitalize;
    letter-spacing: ${bool.label.letterSpacing}px;
    font-family: 'Montserrat', sans-serif;
}
.slider-container {
    margin-left: ${bool.sliderContainer.marginLeft}px;
    background-color: ${bool.sliderContainer.background};
    border-radius: ${bool.sliderContainer.borderRadius}px;
    display: flex;
    flex-direction: row;
    height: ${bool.sliderContainer.height}px;
    justify-content: space-between;
    position: relative;
    width: ${bool.sliderContainer.width}px;
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
    color: ${bool.valueText.fontColor};
    text-transform: ${bool.valueText.textTransform};
    font-weight: ${bool.valueText.fontWeight};
    letter-spacing: ${bool.valueText.letterSpacing}px;
    font-size: ${bool.valueText.fontSize}px;
    user-select: none;
    font-family: 'Montserrat', sans-serif;

}
.slider {
    background-color: ${bool.slider.background};
    border-radius: ${bool.slider.borderRadius}px;
    height: ${bool.slider.height}px;
    left: 0;
    margin: 2px;
    position: absolute;
    top: 0;
    width: ${bool.slider.width}px;
    z-index: 1;
    transition: all ${bool.animation.speed}s ease-out;
}
.slider-checked {
    left: 50.25%;
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
        <div class="data-node">
            <span class="data-text">{left}</span>
        </div>
        <div class="data-node">
            <span class="data-text">{right}</span>
        </div>
        <div class="slider" id="slider"></div>
    </div>
</div>
`;

export class BoolSelect extends HTMLElement {
   // element will listen for changes to the returned attrs
   static get observedAttributes() {
      return ['checked', 'disabled'];
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
    * <bool-select label="Test Label" left="Yes" right="No"></bool-select>
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

   /**
    * getter/setter for drag options
    */
   get dragState() {
      return this.hasAttribute('drag');
   }

   set dragState(val) {
      const canDrag = Boolean(val);
      if (canDrag) {
         this.setAttribute('drag', val);
      } else {
         this.removeAttribute('drag');
      }
   }
   /**
    * getter and setter for state 'checked' and 'disabled'. This bool-select element is treated like
    * a checkbox w/ checked state being either present or not. These atts behave as regular html elements would
    */
   get checked() {
      return this.hasAttribute('checked');
   }
   set checked(val) {
      const isChecked = Boolean(val);
      if (isChecked) {
         this.setAttribute('checked', ''); // set checked present
      } else {
         this.removeAttribute('checked'); // remove it
      }
   }

   get disabled() {
      return this.hasAttribute('disabled');
   }
   set disabled(val) {
      const isDiabled = Boolean(val);
      if (isDiabled) {
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
      shadowRoot.appendChild(boolSelector.content.cloneNode(true));

      this._config = {
         slider: {
            minDiff: 0,
            maxDiff: 141,
         },
      };

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

      // toggle checked state to account for any default values applied. Default is false
      this.toggleChecked();

      // init the listener items
      this.initListeners();

      console.log('this.dragState: ', this.dragState);

      this.internals_.setFormValue(this.checked);
   }

   /**
    * Listen for changes to the attrs defined in the static method observedAttributes
    * name of value changed is passed, as well as the oldValue and the new Value
    */
   attributeChangedCallback(name, oldValue, newValue) {
      // check the name and run your desired method
      if (name === 'checked') {
         this.toggleChecked();
      } else if (name === 'disabled') {
         // TODO - change style on disabled
         this.toggleDisabled();
      }
   }

   /**
    * simple init listener method
    */
   initListeners() {
      // if element is disabled, return null
      if (this.disabled) return;
      this._drag = false; // init _drag

      /**
       * This element is simple toggle, and you can activate the toggle by clicking
       * anywhere in the slider element. The trio element has indexed targets
       */
      this.sliderContainer.addEventListener('click', e => this.mouseClickContainer(e));
      if (!this.dragState) return;
      this.sliderContainer.addEventListener('mousedown', e => this.mouseDownContainer(e));
      this.sliderContainer.addEventListener('mousemove', e => this.mouseMoveContainer(e));
      this.sliderContainer.addEventListener('mouseup', e => this.mouseUpContainer(e));
      //  window.addEventListener('mouseup', e => {
      //    console.log(e.target);
      //    console.log(this);
      //    if (e.target != this) {
      //      console.log('testing window');
      //      this.mouseUpContainer(e);
      //    }
      //  });
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
         // ! I intended to flesh this out more, however a typeof check would be more concise
         if (key.charAt(0).match(/[a-zA-Z]/)) {
            string = string.replace(new RegExp('{' + key + '}', 'gm'), object[key]);
         }
      });
      return string;
   }

   /**
    * toggle the class list of this.slider to set the animation
    */
   toggleChecked() {
      if (this.checked) {
         this.slider.classList.add('slider-checked');
      } else {
         this.slider.classList.remove('slider-checked');
      }
   }

   toggleDisabled() {
      if (this.disabled) {
         this.mainContainer.classList.add('disabled');
      } else {
         this.mainContainer.classList.remove('disabled');
      }
   }
   /**
    * this.dragState = config to allow for dragging
    * this._drag = drag state boolean flag
    * BEGIN manual control of the slider element
    */
   dragStart(e) {
      if (!this.dragState) return;
      // set _drag to true, to bypass the click event above
      this._drag = true;
   }

   dragEnd(e) {
      if (!this.dragState) return;
      // delay _drag to false in order to continue bypass of 'click' event
      setTimeout(() => {
         this.snapSliderElement(e);
         this._drag = false;
      }, 150);
   }

   drag(e) {
      if (!this.dragState) return;
      //
      let diff;

      // set diff to the difference between current mouseX and original mouseX
      diff = e.clientX - this._originXY.x;

      // reset if outside predefined bounds
      if (diff < this._config.slider.minDiff) {
         diff = 0;
      } else if (diff > this._config.slider.maxDiff) {
         diff = this._config.slider.maxDiff;
      }

      // translate the element
      this.dragStateTranslate(diff);
   }

   dragTranslate(diff) {
      if (!this.dragState) return;
      this.slider.style.transform = `translateX(${diff}px)`;
   }
   getSliderContainerBounds() {
      const bounds = this.sliderContainer.getBoundingClientRect();
      return {
         x1: bounds.x,
         x2: bounds.x + bounds.width,
      };
   }
   getSliderBounds() {
      const bounds = this.slider.getBoundingClientRect();
      return {
         x1: bounds.x,
         x2: bounds.x + bounds.width,
      };
   }

   snapSliderElement(e) {
      if (!this.dragState) return;
      /**
       * Use the slider and sliderContainer bounds to determine the center
       * and on which side the slider lands, snap to that side
       */
      const sliderBounds = this.getSliderBounds();
      const sliderContainerBounds = this.getSliderContainerBounds();
      const sCWidth = sliderContainerBounds.x2 - sliderContainerBounds.x1;
      const sWidth = sliderBounds.x2 - sliderBounds.x1;
      const sCCenter = sCWidth / 2;
      const sCenter = sliderBounds.x1 - sliderContainerBounds.x1 + sWidth / 2;
      if (sCenter <= sCCenter) {
         this.dragStateTranslate(0);
         this.checked = false;

         this.internals_.setFormValue(this.checked);
      } else {
         this.dragStateTranslate(141);
         this.checked = true;

         this.internals_.setFormValue(this.checked);
      }
   }
   /**
    * listener containers
    */
   mouseDownContainer(e) {
      if (!this.dragState) return;
      this._originXY = {
         x: e.clientX,
         y: e.clientY,
      };

      /**
       * check slider bounds against mouseX pos and if it's not a hit
       * set _drag to false and return, allowing the 'click' event to pass through
       */
      const sliderBounds = this.getSliderBounds();
      if (this._originXY.x < sliderBounds.x1 || this._originXY.x > sliderBounds.x2) {
         this._drag = false;
         return;
      }

      // if the element is hit, we set the slide variable to true, so we can bypass the click event
      this.dragStateStart(e);
   }
   mouseClickContainer(e) {
      if (this._drag) return;
      if (this.disabled) return;
      // since checked is a boolean value, call the inverse. This change will initiate attributeChangedCallback
      this.checked = !this.checked;

      this.internals_.setFormValue(this.checked);
   }
   mouseUpContainer(e) {
      if (!this.dragState) return;
      this.dragStateEnd(e);
   }
   mouseMoveContainer(e) {
      if (!this.dragState) return;
      //
      if (!this._drag) return;

      this.dragState(e);
   }
}

// define the tag of the custom element.
// customElements.define('bool-select', BoolSelect);
export default BoolSelect;
