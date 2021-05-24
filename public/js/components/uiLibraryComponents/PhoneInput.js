import TextInput from './TextInput.js';

/**
 * PassInput
 *
 * Value is defaul exported with btoa(value), so once caught,
 * it needs to be decoded via atob() in JS or base64_decode in PHP
 */
class PhoneInput extends TextInput {
   constructor() {
      super(); // must call super() first
   }

   /** *****************************************************************************************
    * Set Methods -
    *  * Use these methods to set some states, handle attr changes, etc
    *  * setType changes the input type from text to password
    * **************************************************************************************** */
   setType() {
      this.input.type = 'email';
   }

   setValue() {}
   /** *****************************************************************************************
    * CustomHTML Component Lifecycle Methods
    * **************************************************************************************** */
   connectedCallback() {
      //   this.updateType();
      this.init();

      // change type to password
      this.setType();
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
         this.setValue();
      }
   }
}

export default PhoneInput;
