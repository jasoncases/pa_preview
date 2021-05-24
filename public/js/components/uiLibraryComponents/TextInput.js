import CustomInput from './CustomInput.js';

var textInputTemplate = document.createElement('template');

/**
 * Mobile Browser Override
 */
const modeOverride = window.innerWidth < 600;

// style obj
const style = {
  input: {
    border: `1px solid hsla(0, 0%, 50%, 1)`,
    borderRadius: 5,
    fontSize: modeOverride ? 16 : 18,
    height: modeOverride ? 36 : 50,
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
    marginBottom: modeOverride ? 12 : 24,
  },
  errorText: {
    maxWidth: modeOverride ? 215 : 400,
  },
};

textInputTemplate.innerHTML = `
<style>
    :host {
        font-family: 'Montserrat', sans-serif;
        color: hsla(0, 0%, 25%, 1);
        margin-bottom: 16px;
    }
    select,
    input[type="password"], 
    input[type="email"], 
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
    }

    input[type="email"]:disabled, 
    input[type="password"]:disabled, 
    input[type="text"]:disabled {
        background-color: hsla(0, 0%, 94%, 1);
    }

    input[type="password"] {
      -webkit-text-security: disc;
    }


    .error-text {
        color: red;
        font-size: 12px;
        font-style: italic;
        max-width: ${style.errorText.maxWidth}px;
        text-align: right;
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
    .error-container {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        margin-top: 5px;
        padding-right: 5px;
        width: 100%;
    }
    .data-container {
      display: flex;
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
        white-space: nowrap;

    }
    .input-container {
      width: 100%;
      position: relative;
    }
</style>
<div class="container" id="container">
    <div class="data-container flex-start" id="data-container">
      <div class="label-container">
          <label for="input">{label}</label>
      </div>
      <div class="input-container">
          <input id="input" type="text" name="input" placeholder="{placeholder}" />
      </div>
    </div>
    <div class="error-container">
        <span id="error-text" class="error-text"></span>
    </div>
</div>
`;

/**
 * Input Styled Component Library Element
 *
 * mode = 'span', 'small', 'medium'
 * orientation = 'column', 'row'
 */
class TextInput extends CustomInput {
  static get observedAttributes() {
    return [`disabled`, `err`, `value`, `orientation`];
  }

  /**
   * constructor method
   */
  constructor() {
    super(); // must call super() first

    // create and attach the shadow dom
    const shadowRoot = this.attachShadow({mode: 'open'});

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(textInputTemplate.content.cloneNode(true));

    /**
     *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
     *  */
    this.srPointer = shadowRoot;

    //  this.init();

    this.validationRules = [];
  }

  /**
   *
   * @param {*} reObj {name: '', re: //} name should match an entry in Err
   */
  newRegexRule(reObj) {
    this.validationRules.push(reObj);
  }

  validate() {
    this.clearErr();
    if (this.required) {
      if (this.value == null) {
        this.err = 'required';
        return false;
      }
    }
    return this.validationRules.every((rule) => {
      if (!rule.re.test(this.input.value)) {
        this.err = rule.name;
        return false;
      } else {
        this.clearErr();
        return true;
      }
    });
  }
}

export default TextInput;
