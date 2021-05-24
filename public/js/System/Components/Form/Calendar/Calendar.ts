import {DropdownComponent} from '../../Component/DropdownComponent.js';
import {Trigger} from './Subcomponents/Trigger.js';
import {Dropdown} from './Subcomponents/Dropdown.js';

export class Calendar extends DropdownComponent {
  _id: string = 'ui:form:calendar';

  Trigger: any;
  Dropdown: any;
  ActiveDate: any;

  public constructor() {
    super();
    this._init();
  }

  protected _registerAll() {
    this._registerElementById();
    this._registerSubComponents();
  }

  protected _registerSubComponents() {
    this._registerTrigger(new Trigger(this));
    this._registerDropdown(new Dropdown(this));
    this._registerActiveDate();
    console.log('testing: Calendar: ', this);
  }

  protected _registerTrigger(Trigger: Trigger) {
    this.Trigger = Trigger;
    Trigger.init();
  }

  protected _registerDropdown(Dropdown: Dropdown) {
    this.Dropdown = Dropdown;
    Dropdown.init();
    Dropdown.setDate({
      day: 5,
      month: 9,
      year: 2020,
    });
  }

  protected _registerActiveDate() {}

  protected _setOpenDisplay() {
    console.log('testing _setOpenDisplay()');
    this._element.classList.add('open');
  }

  protected _setClosedDisplay() {
    console.log('testing _setCloseDisplay()');
    this._element.classList.remove('open');
  }
}
