import CalendarSelector from './CalendarSelector.js';

const style = {
   approved: 'hsl(144, 23%, 60%)',
   denied: 'hsl(0, 23%, 60%)',
   pending: 'hsl(64, 60%, 61%)',
   action: 'hsla(23, 85%, 70%, 1)',
   loading: 'hsla(220, 78%, 60%, 1)',
   fontSize: 26,
};

const overrideStyles = document.createElement('template');
overrideStyles.innerHTML = `
<style>
.today {
   background-color: hsla(202, 33%, 75%, 1);
   border-radius: 0;
}
.calendarCell {
   border: 1px solid hsla(0, 0%, 80%, .25);
   margin-top: -1px;
   margin-left: -1px;
}
.calendarCell:hover {
   background-color: hsla(33, 68%, 85%, 1);
   border-radius: 0;
}
.selected {
   border-radius: 0;
   background-color: hsla(33, 68%, 80%, 1);
}
.calendarDayRow {
   flex: none;
   height: fit-content;
}
.calendarDayRow > .calendarDayCell {
   padding: 6px;
}
.calendarContainer {
   height: 360px;
   width: 400px;
   left: -90px;
   background-color: white;
}
.date-container {
   justify-content: flex-end;
   position: relative;
}
.calendarRow {
   background-color: white;
}
.past {
   background-color: hsla(0, 0%, 95%, 1);
   color: hsla(0, 0%, 75%, 1);
}
.approved {
   user-select:none;
   pointer-events:none;
   font-size:${style.fontSize}px;
   position:absolute;
   top:50%;
   margin-top:${-style.fontSize * 0.85}px;
   left:50%;
   margin-left:${-style.fontSize / 2}px;
   color:${style.approved};
}
.denied {
   user-select:none;
   pointer-events:none;
   font-size:${style.fontSize}px;
   position:absolute;
   top:50%;
   margin-top:${-style.fontSize * 0.85}px;
   left:50%;
   margin-left:${-style.fontSize / 2}px;
   color:${style.denied};
}
.pending {
   user-select:none;
   pointer-events:none;
   font-size:${style.fontSize}px;
   position:absolute;
   top:50%; 
   margin-top:${-style.fontSize * 0.85}px;
   left:50%;
   margin-left:${-style.fontSize / 2}px;
   color:${style.pending};
   background-color: transparent;
}
.action {
   user-select:none;
   pointer-events:none;
   font-size:${style.fontSize}px;
   position:absolute;
   top:50%;
   margin-top:${-style.fontSize * 0.85}px;
   left:50%;
   margin-left:${-style.fontSize / 2}px;
   color:${style.action};
}
.loading {
   user-select:none;
   pointer-events:none;
   font-size:${style.fontSize}px;
   position:absolute;
   top:50%;
   margin-top:${-style.fontSize * 0.85}px;
   left:50%;
   margin-left:${-style.fontSize / 2}px;
   color:${style.loading};
}
</style>
`;

const icons = {
   approved: `<span class="approved"><i class="fas fa-check-circle"></i></span>`,
   denied: `<span class="denied"><i class="fas fa-times-circle"></i></span>`,
   pending: `<span class="pending"><i class="fas fa-question-circle"></i></span>`,
   action: `<span class="action"><i class="fas fa-exclamation-circle"></i></span>`,
   loading: `<span class="loading"><i class="fas fa-cog fa-spin"></i></span>`,
};

class RequestCalendarSelector extends CalendarSelector {
   static get observedAttributes() {
      return ['state'];
   }
   get state() {
      return JSON.parse(this.getAttribute('state')) || null;
   }

   set state(val) {
      const hasState = Boolean(val);
      if (hasState) {
         this.setAttribute('state', val);
      } else {
         this.removeAttribute('state');
      }
   }
   constructor() {
      super();

      //  this.injectFontAwesome();
      //  this.overrideStyles();
      //  // this.alertMainContainer();
      //  this.overrideCalendarRowHover();
      //  this.initListeners();
      //  this.lowlightPreviousDays();

      //  this._cacheDateObject = { ...this.dateObject };
   }

   connectedCallback() {
      this.injectFontAwesome();
      this.overrideStyles();
      // this.alertMainContainer();
      this.overrideCalendarRowHover();
      this.updateState();
      this.initListeners();
      this.lowlightPreviousDays();

      this._cacheDateObject = {...this.dateObject};
   }

   /**
    * Listen for changes to the attrs defined in the static method observedAttributes
    * name of value changed is passed, as well as the oldValue and the new Value
    */
   attributeChangedCallback(name, oldValue, newValue) {
      // check the name and run your desired method
      if (name === 'state') {
         this.updateState();
      } else {
         // TODO - change style on disabled
      }
   }

   overrideStyles() {
      this.srPointer.appendChild(overrideStyles.content.cloneNode(true));
   }
   injectFontAwesome() {
      const link = document.createElement('link');
      link.setAttribute('rel', 'stylesheet');
      link.setAttribute('href', 'https://pro.fontawesome.com/releases/v5.8.1/css/all.css');
      link.setAttribute('integry', 'sha384-Bx4pytHkyTDy3aJKjGkGoHPt3tvv6zlwwjc3iqN7ktaiEMLDPqLSZYts2OjKcBx1');
      link.setAttribute('crossorigin', 'anonymous');
      this.srPointer.appendChild(link);
   }
   getDateContainers() {
      this.dates = this.srPointer.querySelectorAll('#date-container');
   }
   alertMainContainer() {
      this.calendarContainer = this.srPointer.getElementById('calendarContainer');
      this.calendarContainer.style.height = `375px`;
      this.calendarContainer.style.width = `400px`;
      this.calendarContainer.style.left = `-90px`;
   }
   overrideCalendarRowHover() {
      const rows = this.srPointer.querySelectorAll('#calRow');
      rows.forEach(row => {
         row.classList.remove('calendarRowHover');
      });
   }
   updateState() {
      this.getDateContainers();

      if (this.state == null) {
         return;
      }

      this.clearAll();

      const CLONE = Array.from(this.state);

      CLONE.forEach(date => {
         const node = Array.from(this.dates).filter(dateNode => {
            return dateNode.getAttribute('data-date') === date.date;
         })[0];

         if (typeof node !== 'undefined') {
            node.innerHTML += icons[date.status];
         }
      });
   }
   initListeners() {
      this.uiComponents.nav.left.addEventListener('mousedown', e => {
         this.removeAttribute('data-date');
         this.runAtPageTurn();
      });
      this.uiComponents.nav.right.addEventListener('mousedown', e => {
         this.removeAttribute('data-date');
         this.runAtPageTurn();
      });
   }

   runAtPageTurn() {
      // reinit listeners and update state
      this.initListeners();
      this.updateState();
      this.overrideCalendarRowHover();
      // gatekeep specific states
      if (this._cacheDateObject.year < this.dateObject.year) return;
      if (this._cacheDateObject.month < this.dateObject.month) return;
      this.lowlightPreviousDays();
   }
   addNewNode(nodeObj) {
      const node = this.srPointer.querySelector('.selected');
      node.innerHTML += icons[nodeObj.status];
   }
   clearAll() {
      Array.from(this.dates).map(node => {
         if (node.firstElementChild) {
            node.firstElementChild.remove();
         }
      });
   }
   /**
    * removes the currently selected node
    */
   removeNode() {
      const node = this.srPointer.querySelector('.selected');
      node.firstElementChild.remove();

      //
   }
   setNodeToLoad(date) {
      const node = Array.from(this.dates).filter(dateNode => {
         return dateNode.getAttribute('data-date') === date;
      })[0];
      if (node.firstElementChild) node.firstElementChild.remove();
      node.innerHTML += icons.loading;
   }
}

export default RequestCalendarSelector;
