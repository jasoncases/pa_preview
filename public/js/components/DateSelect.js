let dateSelect = document.createElement('template');

dateSelect.innerHTML = `
<link
         rel="stylesheet"
         href="https://pro.fontawesome.com/releases/v5.8.1/css/all.css?{rand}"
         integrity="sha384-Bx4pytHkyTDy3aJKjGkGoHPt3tvv6zlwwjc3iqN7ktaiEMLDPqLSZYts2OjKcBx1"
         crossorigin="anonymous"
      />
<style> 
:host {
   --border-radius: 3px;
   color: white;
   position: relative;
   text-transform: none;
   user-select: none;
   text-transform: uppercase;
   box-sizing: border-box;
   font-size: 16px;
   border-radius: var(--border-radius);
   background-color: transparent;
   margin: auto;
   display: inline;
   box-sizing: border-box !important;
   -moz-box-sizing: border-box !important;
   -webkit-box-sizing: border-box !important;
}
.container {
   postion: relative;
   display: flex; 
   flex-direction: column;
   justify-content: center;
   text-align: center;
   width: fit-content;
   min-width: 250px;
   width: 250px;
   max-width: 250px;
   padding: 5px 15px;
   margin: 2px 5px;
   border-radius: var(--border-radius);
   box-sizing: border-box;
   overflow: hidden;
   box-shadow: 1px 1px hsla(0, 0%, 0%, .2), 1px 4px hsla(0, 0%, 0%, .1);

}
.innerDisplayContainer {
   position: absolute;
   padding: 5px 0;
   margin: 2px;
   top: 0;
   left: 0;
   height: fit-content;
   width: 100%;
   box-sizing: border-box;
   -moz-box-sizing: border-box;
   -webkit-box-sizing: border-box;
   font-weight: bold;
}
.innerLinkContainer {
   position: absolute;
   top: 25px;
   width: 100%;
   height: 0;
   visibility: hidden;
   background-color: transparent;
   box-sizing: border-box;
   -moz-box-sizing: border-box;
   -webkit-box-sizing: border-box;
}
.innerLinkContainer > li {
   padding: 5px;
   width: 100%;
   display: flex;
   flex-direction: row;
   justify-content: space-between;
   box-sizing: border-box;
   -moz-box-sizing: border-box;
   -webkit-box-sizing: border-box;
}
.innerLinkContainer > li:hover {
   filter: brightness(120%);

}
.date-range {
}
.schedule-status {
   padding-right: 10px;
}
.open {
   height: fit-content;
   min-height: fit-content;
   max-height: fit-content;
   visibility: visible;
}
ul {
   padding: 0;
   list-style-type: none;
   margin-block-start: 0;
   margin-block-end: 0;
   border-bottom-right-radius: var(--border-radius);
   border-bottom-left-radius: var(--border-radius);
   z-index: 10000;
   border: 1px solid hsl(185, 20%, 30%);
   box-shadow: 1px 1px hsla(0, 0%, 0%, .2), 1px 4px hsla(0, 0%, 0%, .1);
   

}
li {
   padding: 4px 0;
   background-color: hsla(185, 20%, 60%, .9);
}
li:hover {
   filter: brightness(120%);
}
li:last-child {
   border-bottom-right-radius: var(--border-radius);

}
.current {
   filter: brightness(120%);
}
</style>
<div class="container" id="container">
    <div class="innerDisplayContainer" id="innerDisplayContainer">
        {LABELTEXT}
    </div>
    <ul class="innerLinkContainer" id="innerLinkContainer">

    </ul>
</div>
<slot></slot>`;

export class DateSelect extends HTMLElement {
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

   get label() {
      return this.getAttribute('label');
   }

   constructor() {
      super();

      this.rePx = /(.+)px/;

      this.capturedChildren = this.children;
      const shadowRoot = this.attachShadow({mode: 'open'});
      shadowRoot.appendChild(dateSelect.content.cloneNode(true));

      this.container = shadowRoot.getElementById('container');
      this.textContainer = shadowRoot.getElementById('innerDisplayContainer');
      this.linkContainer = shadowRoot.getElementById('innerLinkContainer');

      this.textContainer.innerText = this.label;
   }

   connectedCallback() {
      this.adjustContainerSize();
      this.adjustLinkContainerPlacement();
      this.initListeners();

      const moreUp = '<li> More &#9653; </li>';
      const moreDown = '<li> More &#9663; </li>';
      const style = window.getComputedStyle(this, null);
      const incomingBGColor = style.getPropertyValue('background-color');

      this.container.style.backgroundColor = incomingBGColor;
      this.classList = '';
      const theseChildren = Array.from(this.capturedChildren);
      theseChildren.forEach(child => {
         child.classList = '';
         if (child.getAttribute('data-dateRange') == this.label) {
            child.classList.add('current');
         }
         this.linkContainer.append(child);
         child.addEventListener('mousedown', e => {
            this.textContainer.innerText = child.innerText;
            this.setAttribute('data-scheduleId', child.id);
            this.textContainer.innerText = child.getAttribute('data-dateRange');
            child.classList.add('current');
            this.updateLinkStatus();
         });
      });
      this.linkArray = Array.from(this.linkContainer.children);
   }

   updateLinkStatus() {
      this.linkArray.map(link => {
         if (link.id !== this.getAttribute('data-scheduleId')) {
            link.classList.remove('current');
         }
      });
   }
   initListeners() {
      this.container.addEventListener('click', e => {
         this.setAttribute('data-scheduleId', null);

         this.toggleOpen();
      });
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
         this.linkContainer.classList.add('open');
         // this.container.classList.add('open');
      } else {
         this.setAttribute('open', '');
         // this.container.classList.remove('open');
         this.linkContainer.classList.remove('open');
      }
   }

   toggleSelected() {
      if (this.selected) {
         this.removeAttribute('selected');
      } else {
         this.setAttribute('selected', '');
      }
   }

   adjustContainerSize() {
      const oCStyle = window.getComputedStyle(this.textContainer);
      const oCHeight = oCStyle.getPropertyValue('height');
      const oCWidth = oCStyle.getPropertyValue('width');

      this.container.setAttribute(`style`, `width:${oCWidth - 10} !important;height:${oCHeight} !important;`);
   }

   adjustLinkContainerPlacement() {
      const topAdjustment = -2; // adjust due to each border pixel width
      const oCStyle = window.getComputedStyle(this.container);
      const marginLeft = oCStyle.getPropertyValue('margin-left');
      var top = oCStyle.getPropertyValue('height').match(this.rePx);
      top = Number(top[1]) - topAdjustment;
      var width = oCStyle.getPropertyValue('width').match(this.rePx);
      width = Number(width[1]) + topAdjustment;
      this.linkContainer.setAttribute('style', `left:${marginLeft};top:${top}px;width:${width}px`);
   }
}

export default DateSelect;
