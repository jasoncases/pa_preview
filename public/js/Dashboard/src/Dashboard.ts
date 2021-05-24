import {Ui} from './Components/Ui.js';

export class Dashboard {
  Ui: Ui;
  public constructor() {
    this._init();
  }

  private _init() {
    this._registerAll();
  }

  private _registerAll() {
    this._registerUi(new Ui());
  }

  private _registerUi(Ui: Ui) {
    this.Ui = Ui;
    Ui.Dashboard = this;
    Ui.init();
  }
}
