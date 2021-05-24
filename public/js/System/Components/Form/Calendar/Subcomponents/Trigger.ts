import {CalendarChildComponent} from './CalendarChildComponent.js';

export class Trigger extends CalendarChildComponent {
  _id: string = 'trigger';

  //
  protected _initListeners() {
    this._element.addEventListener('click', (event) =>
      this._mouseClickContainer(event),
    );
  }

  protected _action(event: MouseEvent) {
    this.Calendar.toggleOpen();
  }
}
