import CalendarSelector from './CalendarSelector.js';

class TriCalendarSelector extends CalendarSelector {
   constructor() {
      super();
      console.log('THIS IS A TRICALENDAR SELECTOR');
      console.log('templateParser: ', this.templateParser);
   }
}

export default TriCalendarSelector;
