import { outsideClick } from '../System/Lib/Lib.js';
import { cp } from './componentProps.js'
import TemplateParser from '/js/components/class/TemplateParser.js'


const colorArray = [
  // colors per jason on 2.12.21
  '#430CED', // blue - white text
  '#0CF7F0', // cyan - black text
  '#6CE000', // green - black text
  '#F7A90C', // orange - black text
  '#F00101', // red - white text
  '#F024B7', // fuschia - white text
  '#8F45F0', // purple - white text
  '#FAEE44', // yellow - black text
  '#9C5E32', // brown - white text
  '#FEBBDD', // pink - black text
  '#0d0e11', // black - white text
  '#6b6b6b', // darkgrey - white text
];
/**
 * instatiate the template element
 */
let colorPickerTemplate = document.createElement('template')

/**
 * Set innerHTML of the template element. This element will get appended to the shadow root
 */
colorPickerTemplate.innerHTML = `
<style>
:host {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
}
.container {
    width: fit-content;
    display: flex;
    flex-direction: column;
    justify-content: left;
    background-color: hsl(194, 23%, 94%);  
    height: 35px;
    width: fit-content;
    min-width: 300px;
    border-radius: 3px;
    border: 1px solid rgb(22, 60, 78);
    position: relative;

}
.container div, 
.container span {
  pointer-events: all;
  user-select: none;
}
.label-container {
    height: 100%;
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    text-align: center;

}
.label-container > span {
    margin: auto 8px;
}
.label-text {
    flex: 1;
    text-align: left;
    font-size: 16px;
}
.caret {
    display: inline-block;
    width: 0;
    height: 0;
    margin: 0 auto;
    vertical-align: middle;
    border-top: 6px solid hsla(195, 53%, 45%, 1);
    border-right: 6px solid transparent;
    border-left: 6px solid transparent;
    transform: rotate(180deg);
    transition: transform .25s ease-out;
    // background-color: green;
}
.picker-row {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    width: 20%;
    padding: 6px;
    height: 45px;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
}
.picker-row:hover {
    background-color: hsl(194, 23%, 83%);  
    cursor: pointer;
}
.picker-container {
    position: absolute;
    top: 100%;
    left: 0;
    height: fit-content;
    width: 100%;
    transition: all .2s ease-out;
    background-color: white;
    overflow: hidden;
    border: 1px solid black;
    z-index: 10000;
    margin-top: 2px;
    border-radius: 3px;
    opacity: 1;
    display: flex;
    flex-direction: flex-start;
    flex-wrap: wrap;
   }

   .collapse > .picker-container {
      height: 0px;
      border: 1px solid white;
      opacity: 0;
}
.collapse > .label-container > .caret {
    transform: rotate(0deg);
}
.picker-badge {
    height: 100%;
    width: 100%;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
}
.preview-badge {
    height: 30px;
    width: 30px;
    border-radius: 5px;
    margin: auto 15px;
}
</style>
<div class="container" id="container">
    <div class="label-container">
        <span class="preview-badge" id="preview"></span>
        <span class="label-text">Select a color</span>
        <span class="caret"></span>
    </div>
    <div class="picker-container" id="picker-container">
        
    </div>
</div>
    `

let pickerInnerTemplate = document.createElement('template')
pickerInnerTemplate.innerHTML = `<span class="picker-badge" style="background-color:{color}" data-color="{color}"></span>`

let previewBadge = `<span class="preview-badge"></span>`

export class ColorPicker extends HTMLElement {
  // element will listen for changes to the returned attrs
  static get observedAttributes() {
    return ['open', 'state', 'disabled', 'value']
  }

  /**
   * states is a string of color values, separated by commas
   */
  get state() {
    return this.hasAttribute('state')
      ? this.getAttribute('state').split(',')
      : colorArray;
      // * the below colors were removed in favor of the colors in array
      // * defined above, provided by Jason (2.12.21)
      // : '#FDB7B9,#8E2729,#576C90,#0C2042,#8EE28E,#0D840D,#E7964C,#A55710,#4C98A3,#0C5A65'.split(
      //     ','
      //   )
  }
  set state(val) {
    const hasState = bool(val)
    if (hasState) {
      this.setAttribute('state', val)
    } else {
      this.removeAttribute('state')
    }
  }

  /**
   * getter and setter for state 'checked' and 'disabled'. This bool-select element is treated like
   * a checkbox w/ checked state being either present or not. These atts behave as regular html elements would
   */
  get open() {
    return this.hasAttribute('open')
  }
  set open(val) {
    const isOpen = Boolean(val)
    if (isOpen) {
      this.setAttribute('open', '') // set open present
    } else {
      this.removeAttribute('open') // remove it
    }
  }
  get value() {
    return this.getAttribute('value')
  }
  set value(val) {
    const isSet = Boolean(val)
    if (isSet) {
      this.setAttribute('value', val) // set open present
    } else {
      this.removeAttribute('value') // remove it
    }
  }
  get disabled() {
    return this.hasAttribute('disabled')
  }
  set disabled(val) {
    const isDiabled = Boolean(val)
    if (isDiabled) {
      this.setAttribute('disabled', '')
    } else {
      this.removeAttribute('disabled')
    }
  }

  /**
   * constructor method
   */
  constructor() {
    super() // must call super() first

    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({ mode: 'open' })

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(colorPickerTemplate.content.cloneNode(true))

    this._config = {
      slider: {
        minDiff: 0,
        maxDiff: 141,
      },
    }

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot

    // get main container element so we can template the values on load
    this.mainContainer = shadowRoot.getElementById('container')

    // get slider container and the slider element to deal with animation and click events
    this.pickerContainer = shadowRoot.getElementById('picker-container')

    // toggle checked state to account for any default values applied. Default is false
    this.toggleOpen()

    this.template = new TemplateParser()
    // init the listener items
    this.initListeners()
    this.templateValues()
    outsideClick(this)
  }

  getAncestor(){
    return this.mainContainer
  }

  close(){
    this._close()
    this.open = false;
  }
  /**
   * Listen for changes to the attrs defined in the static method observedAttributes
   * name of value changed is passed, as well as the oldValue and the new Value
   */
  attributeChangedCallback(name, oldValue, newValue) {
    console.log('name: ', name)
    // check the name and run your desired method
    if (name === 'open') {
      console.log('CALLING TOGGLE OPEN', this.open)
      this.toggleOpen()
    } else if (name === 'value') {
      this.setPreviewBadge()
      // TODO - change style on disabled
    }
  }

  setPreviewBadge() {
    const preview = this.srPointer.getElementById('preview')
    preview.style.backgroundColor = this.value
  }
  /**
   * simple init listener method
   */
  initListeners() {
    // if element is disabled, return null
    if (this.disabled) return

    this.mainContainer.addEventListener(
      'click',
      (e) => (this.open = !this.open)
    )
  }

  /**
   * capture the values and then replace them within the innerHTML of the main container
   */
  templateValues() {
    // create an object w/ the props that were caught by the earlier getters
    this.state.forEach((color) => {
      const el = document.createElement('div')
      el.innerHTML = this.template.templateText(pickerInnerTemplate.innerHTML, {
        color: color,
      })
      el.style.width = `${(100/colorArray.length*2)}%`
      el.classList.add('picker-row')
      this.pickerContainer.appendChild(el)

      el.addEventListener('click', (e) => {
        console.log('tesing her')

        this.setValue(color)
      })
    })
  }

  setValue(value) {
    this.value = value
    const formNode = this.nextElementSibling
    if (formNode) {
      if ((formNode.id = 'colorPickerColorOutput')) {
        formNode.value = value
      }
    }
  }

  /**
   * toggle the class list of this.slider to set the animation
   */
  toggleOpen() {
    if (this.open) {
      this._open();
    } else {
      this._close()
    }
  }
  _open(){
    this.mainContainer.classList.remove('collapse')
  }
  _close(){
    this.mainContainer.classList.add('collapse')
  }
}

// define the tag of the custom element.
// customElements.define('bool-select', BoolSelect);
export default ColorPicker
