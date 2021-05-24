import TemplateParser from './class/TemplateParser.js';

//
const tutorialNodeTemplate = document.createElement('template');

tutorialNodeTemplate.innerHTML = `
<style>
    :host {
        position: absolute;
        
    }
    .container {
        position: absolute;
        // top: 300px;
        // left: 800px;
        height: 20px;
        width: 20px;
        cursor: pointer;
        z-index: 100000;
    }
    .text-container {
        display: none;
        flex-direction: column;
        justify-content: start;
        padding: 15px;
        font-size: 14px;
        font-weight: 400;
        position: absolute;
        top: 0;
        left: calc(100% + 25px);
        border-radius: 5px;
        border: 1px solid black;
        background-color: hsla(54, 100%, 89%, 0.9);
        min-height: 40px;
        min-width: 150px;
        box-shadow: 2px 1px hsla(0, 0%, 0%, .25), 2px 3px hsla(0, 0%, 0%, .15);
    }
    .text-imp {
        font-weight: 600;
    }
    .node-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: 1px solid hsla(0, 50%, 75%, .05);
        height: 100%;
        width: 100%;
        pointer-events: none;
    }
    .node {
        pointer-events: none;
        position: absolute;
        user-select: none;
        display: none;
        margin: auto;
        background-color: hsla(0, 0%, 100%, 1);
        border-radius: 50%;
        border: 1px solid hsla(195, 100%, 60%, 1);
        transform-origin: 50% 50%;
    }
    .animate {
        display: block;
        animation-name: node-anim;
        animation-duration: .6s;
        animation-iteration-count: infinite;
    }
    @keyframes node-anim {
        0% {
            height: 1px;
            width: 1px;
            background-color: hsla(0, 0%, 100%, 1);
            border: 1px solid hsla(195, 100%, 50%, 1);
        }
        100% {
            height: 25px;
            width: 25px;
            background-color: hsla(0, 0%, 100%, 0);
            border: 7px solid hsla(195, 100%, 50%, 0);
        }
    }
    .container:hover > .text-container {
        display: flex;
    }
    .complete {
        display: none;
    }
</style>
    <div id="container" class="container">
        <div class="node-container">
            <div class="node" id="node"></div>
        </div>
        <div class="text-container" id="text-container">
            {text}
        </div>
    </div>
`;

export class TutorialNode extends HTMLElement {
   static get observedAttributes() {
      return ['complete', 'disabled', 'animate'];
   }

   get order() {
      return this.getAttribute('order') || null;
   }

   set order(val) {
      isSet = Bool(val);
      if (isSet) {
         this.setAttribute('order', val);
      } else {
         this.removeAttribute('order');
      }
   }

   get text() {
      return this.getAttribute('text');
   }

   set text(val) {
      const isSet = Boolean(val);
      if (isSet) {
         this.setAttribute('text', val);
      } else {
         this.removeAttribute('text');
      }
   }

   get complete() {
      return this.hasAttribute('complete');
   }

   set complete(val) {
      const isSet = Boolean(val);
      if (isSet) {
         this.setAttribute('complete', val);
      } else {
         this.removeAttribute('complete');
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
   get animate() {
      return this.hasAttribute('animate');
   }

   set animate(val) {
      const isSet = Boolean(val);
      if (isSet) {
         this.setAttribute('animate', '');
      } else {
         this.removeAttribute('animate');
      }
   }
   get target() {
      return this.getAttribute('target');
   }
   set target(val) {
      const isSet = Boolean(val);
      if (isSet) {
         this.setAttribute('target', val);
      } else {
         this.removeAttribute('target');
      }
   }
   constructor() {
      super();

      const shadowRoot = this.attachShadow({mode: 'open'});

      // append the template element above w/ cloneNode
      shadowRoot.appendChild(tutorialNodeTemplate.content.cloneNode(true));

      /**
       *  set the shadowRoot to a object level prop pointer. Can't set to 'this' at inital creation
       *  */
      this.srPointer = shadowRoot;

      this.mainContainer = shadowRoot.getElementById('container');
      this.textContainer = shadowRoot.getElementById('text-container');
      this.node = shadowRoot.getElementById('node');

      this.templates = {
         text: this.textContainer.innerHTML,
      };
      this.templateParser = new TemplateParser();

      this.initListeners();

      this.templateText();

      //   this.scatter();
   }

   scatter() {
      this.mainContainer.style.top = `${400 + Math.floor(Math.random() * 250)}px`;
      this.mainContainer.style.left = `${400 + Math.floor(Math.random() * 380)}px`;
   }

   attributeChangedCallback(name, oldValue, newValue) {
      if (name === 'complete') {
         this.toggleComplete();
      } else if (name === 'animate') {
         this.toggleAnimate();
      }
   }

   initListeners() {
      this.mainContainer.addEventListener('mousedown', e => {
         // action to complete on container element
         this.complete = !this.complete;
      });
   }

   /**
    * Allow the TUTORIAL class to reinit should the user want to review
    */
   review() {
      if (this.complete) {
         this.complete = !this.complete;
      }
   }

   /**
    * toggle complete state
    */
   toggleComplete() {
      if (this.complete) {
         this.mainContainer.classList.add('complete');
      } else {
         this.mainContainer.classList.remove('complete');
      }
   }

   templateText() {
      const obj = {
         text: this.text,
      };
      this.textContainer.innerHTML = this.templateParser.templateText(this.templates['text'], obj);
   }
   toggleAnimate() {
      if (this.animate) {
         this.setAnimateState();
      } else {
         this.clearAnimateState();
      }
   }
   setAnimateState() {
      this.node.classList.add('animate');
   }
   clearAnimateState() {
      this.node.classList.remove('animate');
   }
}

export default TutorialNode;
