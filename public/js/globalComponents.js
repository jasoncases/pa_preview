/** *****************************************************************************
 *
 * Imports
 *
 ***************************************************************************** */

// Misc System Family Components
import ColorPicker from './components/ColorPicker.js'
import TutorialNode from './components/TutorialNode.js'
import { ScheduleStatus } from './Schedule/src/Components/Status/schedule_status.js'
import TriCalendarSelector from './components/TriCalendarSelector.js'
import RequestCalendarSelector from './components/RequestCalendarSelector.js'
import LoaderElement from './components/LoaderElement.js'

// Form Family Components
import TextInput from './components/uiLibraryComponents/TextInput.js'
import EmailInput from './components/uiLibraryComponents/EmailInput.js'
import PhoneInput from './components/uiLibraryComponents/PhoneInput.js'
import PassInput from './components/uiLibraryComponents/PassInput.js'
import SelectInput from './components/uiLibraryComponents/SelectInput.js'
import MultiSelect from './components/uiLibraryComponents/MultiSelect.js'
import uiCheck from './components/Checkbox.js'
import BoolSelect from './components/BoolSelector.js'
import TrioSelect from './components/TrioSelector.js'
import Table from './components/Table.js'
import DateTime from './components/DateTime/DateTime.js'
import DateTimeDate from './components/DateTime/DateTimeDate.js'
import DateTimeTime from './components/DateTime/DateTimeTime.js'

// Snapshot Family Components

// TESTING
import HelloWorld from './components/uiLibraryComponents/TestComponent.js'

/** *****************************************************************************
 *
 * Definitions
 *
 ***************************************************************************** */

// Form Family Components
customElements.define( 'ui-checkbox', uiCheck )
customElements.define( 'bool-select', BoolSelect )
customElements.define( 'trio-select', TrioSelect )
customElements.define( 'text-input', TextInput )
customElements.define( 'pass-input', PassInput )
customElements.define( 'email-input', EmailInput )
customElements.define( 'phone-input', PhoneInput )
customElements.define( 'select-input', SelectInput )
customElements.define( 'multi-select', MultiSelect )
customElements.define( 'table-view', Table )
customElements.define( 'date-time', DateTime )
customElements.define( 'datetime-date', DateTimeDate )
customElements.define( 'datetime-time', DateTimeTime )

// TEST
customElements.define( 'hello-world', HelloWorld )

// Snapshot Family Components

// Misc System Family Components
customElements.define( 'color-picker', ColorPicker )
customElements.define( 'tutorial-node', TutorialNode )
customElements.define( 'tricalendar-selector', TriCalendarSelector )
customElements.define( 'request-calendar', RequestCalendarSelector )
customElements.define( 'loader-element', LoaderElement )
customElements.define( 'schedule-status', ScheduleStatus )

function componentHasMethod( element, method ) {
  if ( typeof element[ method ] === 'function' ) {
    console.log( `${ element } has a method called ${ method }` )
  } else {
    console.log( `${ method } not found on element: ${ element }` )
  }
}
