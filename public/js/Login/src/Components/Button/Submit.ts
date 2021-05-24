import {Component} from '../../../../System/Components/Component/Component.js';
import {Login} from '../../Login.js';

export class Submit extends Component {
  Login: Login;
  _id: string = 'ui:login:submit';

  public constructor() {
    super();
    this._init();
  }

  protected _registerAll() {
    this._registerElementById();
  }

  protected _initListeners() {
    console.log('test');
    this._element.addEventListener('click', (event) =>
      this._mouseClickContainer(event),
    );
    window.addEventListener('keypress', (event) =>
      this._keypressContainer(event),
    );
  }

  protected _action(event: MouseEvent) {
    console.log('testing here: ', this.Login);
    this.Login.submit();
  }

  protected _keypressContainer(event: KeyboardEvent) {
    if (event.keyCode !== 13) return;
    this.Login.submit();
  }
}
