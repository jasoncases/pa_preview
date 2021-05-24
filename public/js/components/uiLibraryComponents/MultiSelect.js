import SelectInput from './SelectInput.js';

/**
 * Value is a JSON string, which must be decoded, JS: JSON.parse(name), PHP: json_decode(name)
 */
class MultiSelect extends SelectInput {
   constructor() {
      super();

      this.values = [];

      this.multi = true;
   }

   connectedCallback() {
      this.init();

      this.extendCaptures();

      this.swapOptions();

      this.initSelectListener();
   }

   setSelectValue(el) {
      const val = el.value;
      if (this.values.indexOf(val) >= 0) {
         this.values = this.values.filter(item => {
            return item !== val;
         });
      } else {
         this.values.push(val);
      }

      this.value = this.values.length <= 0 ? null : JSON.stringify(this.values);
      this.internals_.setFormValue(this.value);
   }
   setPreviewText(el) {
      var preview = '';
      if (this.values.length <= 0) {
         preview = this.options[0].innerText;
      } else if (this.values.length < 2) {
         preview = el.innerText;
      } else {
         preview = `(${this.values.length}) Elements Selected`;
      }
      this.previewContainer.innerText = preview;
   }
}

export default MultiSelect;
