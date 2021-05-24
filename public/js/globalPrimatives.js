import {DateSelect} from './components/DateSelect.js';
import {CalendarSelector} from './components/CalendarSelector.js';
/**
 * Library of Global Primative Design elements
 */
let customSelectTemplate = document.createElement('template');
customSelectTemplate.innerHTML = `<style>
:host {
  font-size: 12px;
  border-radius: 0;
  width: fit-content;
  margin: 5px;
  user-select: none;
  pointer-events: all;
  display: flex;
  flex-direction: row;
  justifty-content: flex-start;
  background-color: transparent;
}
.cst-main {
  margin-right: 0;
  border: 1px solid black;
  padding: 4px 6px;
  border-top-left-radius: 3px;
  border-bottom-left-radius: 3px;
  background-color: green;
}
.cst-action {
  border: 1px solid black;
  border-left: none;
  padding: 4px 7px;
  border-top-right-radius: 3px;
  border-bottom-right-radius: 3px;
}
.cst-main:hover {
  filter: brightness(150%);
}
.brighten-more: {
  filter: saturate(50%);
}
</style>
<div class="cst-main" id="cst-main">{label}</div><div class="cst-action" id="cst-action">&#8895;</div>
<slot></slot>`;

class CustomSelect extends HTMLElement {
   get open() {
      return this.hasAttribute('open');
   }

   set open(val) {
      if (val) {
         this.setAttribute('open', true);
      } else {
         this.removeAttribute('open');
      }
      this.toggleOpen();
   }

   get disabled() {
      return this.hasAttribute('disabled');
   }

   set disabled(val) {
      if (val) {
         this.setAttribute('disabled', '');
      } else {
         this.removeAttribute('disabled');
      }
   }

   get label() {
      return this.getAttribute('label');
   }

   get inlineStyle() {
      return this.getAttribute('sdstyle');
   }
   constructor() {
      super();
      this.state = {};
      this.addEventListener('click', e => {
         if (this.disabled) return;
         this.toggleOpen();
      });

      const template = new Template();
      this.state['label'] = this.label;

      // HARD CSS PROPERTY VALUES
      // this.style.backgroundColor = 'transparent';

      let shadowRoot = this.attachShadow({mode: 'open'});
      shadowRoot.appendChild(customSelectTemplate.content.cloneNode(true));

      this.main = shadowRoot.getElementById('cst-main');
      this.sub = shadowRoot.getElementById('cst-action');

      this.main.addEventListener('mouseover', e => {
         //  this.main.classList.toggle('brighten');
      });

      if (this.inlineStyle !== null) {
         const catchInlineStyles = this.inlineStyle.split(';') || null;
         this.inlineStyles = [];
         if (catchInlineStyles.length > 0) {
            catchInlineStyles.forEach(style => {
               var key = style.split(':')[0];
               var value = style.split(':')[1];
               if (typeof value === 'undefined') return;
               this.inlineStyles.push({key: key, value: value});
            });
         }
      }

      var srChildren = Array.from(shadowRoot.children);
      srChildren.forEach(child => {
         var iText = child.innerHTML;
         iText = template.replace(iText, this.state);
         child.innerHTML = iText;
         if (typeof this.inlineStyles === 'undefined') return;
         this.inlineStyles.forEach(style => {
            child.style[style.key] = style.value;
         });
      });
   }

   toggleOpen() {
      if (this.open) {
         this.removeAttribute('open');
      } else {
         this.setAttribute('open', '');
      }
   }
}

customElements.define('custom-select', CustomSelect);

var borderRadius = 5;

let drawerButtonTemplate = document.createElement('template');
drawerButtonTemplate.innerHTML = `<style>
:host {
   --background-color: rgb(65, 160, 163);
   --border-radius: ${borderRadius}px;
   display: inline-block;
   color: white;
   position: relative;
   text-transform: none;
   margin: 2px 5px;
   user-select: none;
   // text-transform: uppercase;
}
.db-outer-container {
   font-size: 16px;
   font-weight: bold;
   display: flex;
   flex-direction: row;
   justify-content: flex-start;
   position: absolute;
}
.db-inner-container {
   position: relative;
   top: 0;
   left: 0;
   width: auto;
   height: auto;
   display: flex;
   flex-direction: column;
   justify-content: flex-start;
}

.db-main {
   display: flex;
   flex-direction: column;
   justify-content: center;
   text-align: center;
   padding: 8px 15px;
   background-color: var(--background-color);
   border-top-left-radius: var(--border-radius);
   border-bottom-left-radius: var(--border-radius);
}
.db-hover:hover {
   filter: brightness(125%);
}
.db-children-container {
   display: flex;
   flex-direction: column;
   justify-content: flex-start;
   background-color: var(--background-color);
   transition: max-height .5s ease-in;
   max-height: 450px;
   width: fit-content;
   min-width: 150px;
   overflow: hidden;
   border-radius: 0;
}
.db-child-item {
   padding: 5px;
   font-size: 12px;
   background-color: var(--background-color);
   display: flex;
   flex-direction: row;
   justify-content: center;
   filter: saturate(75%) brightness(125%);
   border:none;
   border-bottom: 1px solid gray;
   color: white;
}
.db-child-item:last-child {
   border: none;
}
.db-child-item:hover {
   filter: brightness(90%);
}
.db-child-item:focus {
   outline: none;
}
.db-action {
   display: flex;
   flex-direction: column;
   justify-content: center;
   text-align: center;
   font-size: 10px;
   background-color: var(--background-color);
   border-bottom-right-radius: var(--border-radius);
   border-top-right-radius: var(--border-radius);
   width: 20px;
   max-height: 34px;
}
.db-action-icon {
   transform-origin: 50% 50%;
   transition: all .2s ease-out;
}
.db-rotate {
   transform: rotate(-180deg);
}
.db-collapse {
   height: 0;
   max-height: 0;
}
.db-selected {
   filter: brightness(140%);
}

.brighten-more: {
  filter: saturate(50%);
}
</style>
<div class="db-outer-container" id="outerContainer">

   <div class="db-inner-container" id="innerContainer">

      <div class="db-main db-hover" id="main">{label}</div>
      
      <div class="db-children-container db-collapse" id="container">

      </div>

   </div>

   <div class="db-action db-hover" id="action"><span class="db-action-icon" id="action-icon">&#9662;</span></div>

</div>
<slot></slot>`;

class DrawerButton extends HTMLElement {
   get open() {
      return this.hasAttribute('open');
   }

   set open(val) {
      if (val) {
         this.setAttribute('open', true);
      } else {
         this.removeAttribute('open');
      }
      this.toggleOpen();
   }

   get selected() {
      return this.hasAttribute('selected');
   }

   set selected(val) {
      if (val) {
         this.setAttribute('selected', true);
      } else {
         this.removeAttribute('selected');
      }
   }

   get disabled() {
      return this.hasAttribute('disabled');
   }

   set disabled(val) {
      if (val) {
         this.setAttribute('disabled', '');
      } else {
         this.removeAttribute('disabled');
      }
   }

   get label() {
      return this.getAttribute('label');
   }
   get autoClose() {
      return this.getAttribute('auto-close');
   }

   constructor() {
      super();
      this.state = {};

      this.capturedChildren = this.children;

      // If there is innerText, use that if label isn't present. Label takes precedent over innerText
      const thisInnerText = this.innerText;

      // HARD CSS PROPERTY VALUES
      // this.style.backgroundColor = 'transparent';

      let shadowRoot = this.attachShadow({mode: 'open'});
      shadowRoot.appendChild(drawerButtonTemplate.content.cloneNode(true));

      // acquire children
      this.allowedCSSProperties = {
         backgroundColor: ['main', 'action', 'container'],
         borderRadius: ['main', 'action', 'container'],
         color: ['main', 'action', 'container'],
         fontWeight: ['main', 'action', 'container'],
      };

      // ! It may make more sense to list the properties to allow to change from user styles
      // acquire --MAIN-- element & establish incoming properties to ignore
      this.main = shadowRoot.getElementById('main');
      this.mainAllowed = [];
      // acquire --ACTION-- element & establish incoming properties to ignore
      this.action = shadowRoot.getElementById('action');
      this.actionIgnore = [];
      this.actionIcon = shadowRoot.getElementById('action-icon');
      // acquire --CONTAINER-- element & establish incoming properties to ignore
      this.container = shadowRoot.getElementById('container');
      this.containerIgnore = [];
      // acquire --CHILDREN-- elements & establish incoming properites to ignore
      this.childItems = shadowRoot.querySelectorAll('#db-child');
      this.childItemsIgnore = [];

      this.outerContainer = shadowRoot.getElementById('outerContainer');

      this.action.addEventListener('click', e => {
         // if (!this.selected) return;
         if (this.disabled) return;

         const getObj = {...JSON.parse(this.getAttribute('data-set'))};

         getObj.action = 'change';

         this.setAttribute('data-get', JSON.stringify(getObj));

         if (!this.selected) this.toggleSelected();
         this.toggleOpen();
      });
      this.main.addEventListener('click', e => {
         if (this.disabled) return;
         this.setAttribute('data-get', this.getAttribute('data-set'));
         if (!this.selected) this.toggleSelected();
         if (!this.open) return;
         this.toggleOpen();
      });
      this.addEventListener('mouseout', e => {
         if (!this.autoClose) return;
         this.autocloseTimer = setTimeout(() => {
            if (!this.open) return;
            this.toggleOpen();
         }, 3000);
      });
      this.addEventListener('mouseover', e => {
         if (typeof this.autocloseTimer === 'undefined') return;
         clearTimeout(this.autocloseTimer);
      });

      if (this.selected) this.main.classList.add('db-selected');
   }

   connectedCallback() {
      this.state['label'] = this.label;
      this.templateReplaceText(this.main, this.state);

      const theseChildren = Array.from(this.capturedChildren);
      theseChildren.forEach(child => {
         child.classList = '';
         child.classList.add('db-child-item');

         this.container.append(child);
      });

      this.capturedChildren = Array.from(this.container.children);

      this.capturedChildren.forEach(child => {
         this.returnAction = child.addEventListener('click', e => {
            this.toggleOpen();
            this.setAttribute('data-get', child.getAttribute('data-set'));
         });
      });

      this.innerHTML = '';
      this.adjustContainerSize();
   }
   adjustContainerSize() {
      const oCStyle = window.getComputedStyle(this.outerContainer);
      const oCHeight = oCStyle.getPropertyValue('height');
      const oCWidth = oCStyle.getPropertyValue('width');

      this.setAttribute(`style`, `width:${oCWidth} !important;height:${oCHeight} !important;`);
   }
   templateReplaceText(element, state) {
      const keys = Object.keys(state);
      keys.forEach(key => {
         const re = new RegExp('{' + key + '}', 'g');
         let iT = element.innerHTML;
         iT = iT.replace(re, state[key]);
         element.innerHTML = iT;
      });
   }
   toggleOpen() {
      if (this.open) {
         this.removeAttribute('open');
         this.container.classList.add('db-collapse');
         this.actionIcon.classList.remove('db-rotate');
      } else {
         this.setAttribute('open', '');
         this.container.classList.remove('db-collapse');
         this.actionIcon.classList.add('db-rotate');
      }
   }
   toggleSelected() {
      if (this.selected) {
         this.removeAttribute('selected');
         this.main.classList.remove('db-selected');
      } else {
         this.setAttribute('selected', '');
         this.main.classList.add('db-selected');
      }
   }
}

customElements.define('drawer-button', DrawerButton);

// BEGIN TEMPLATE BELOW:
let tempalateTemplate = document.createElement('template');
tempalateTemplate.innerHTML = `<style>
:host {
   --background-color: rgb(65, 160, 163);
   --border-radius: 3px;
}
** CSS & HTML GO HERE
<slot></slot>`;

class TempalateTemplate extends HTMLElement {
   get open() {
      return this.hasAttribute('open');
   }

   set open(val) {
      if (val) {
         this.setAttribute('open', true);
      } else {
         this.removeAttribute('open');
      }
      this.toggleOpen();
   }

   get selected() {
      return this.hasAttribute('selected');
   }

   set selected(val) {
      if (val) {
         this.setAttribute('selected', true);
      } else {
         this.removeAttribute('selected');
      }
   }

   constructor() {
      super();

      let shadowRoot = this.attachShadow({mode: 'open'});
      shadowRoot.appendChild(drawerButtonTemplate.content.cloneNode(true));
   }

   connectedCallback() {}

   templateReplaceText(element, state) {
      const keys = Object.keys(state);
      keys.forEach(key => {
         const re = new RegExp('{' + key + '}', 'g');
         let iT = element.innerHTML;
         iT = iT.replace(re, state[key]);
         element.innerHTML = iT;
      });
   }
   toggleOpen() {
      if (this.open) {
         this.removeAttribute('open');
         this.container.classList.add('db-collapse');
         this.actionIcon.classList.remove('db-rotate');
      } else {
         this.setAttribute('open', '');
         this.container.classList.remove('db-collapse');
         this.actionIcon.classList.add('db-rotate');
      }
   }
   toggleSelected() {
      if (this.selected) {
         this.removeAttribute('selected');
         this.main.classList.remove('db-selected');
      } else {
         this.setAttribute('selected', '');
         this.main.classList.add('db-selected');
      }
   }
}

// customElements.define('drawer-button', tempalateTemplate);

customElements.define('date-select', DateSelect);
customElements.define('calendar-selector', CalendarSelector);
