import {CustomWorkTypes} from '../../../CustomWorkTypes/src/CustomWorkTypes.js';
import {Dashboard} from '../Dashboard.js';
import {Menu} from './Menu/Menu.js';
import {Snapshot} from '../../../Snapshot/src/Snapshot.js';

export class Ui {
  Menu: Menu;
  Dashboard: Dashboard;
  CustomWorkTypes: CustomWorkTypes;
  Snapshot: Snapshot;

  public init() {
    this._init();
  }

  private _init() {
    this._registerAll();
    this._registerCustomWorkTypes(new CustomWorkTypes());
    this._registerSnapshot(new Snapshot());
  }

  private _registerSnapshot(Snapshot: Snapshot) {
    this.Snapshot = Snapshot;
    Snapshot.DashboardUi = this;
    Snapshot.init();
  }
  private _registerCustomWorkTypes(CWT: CustomWorkTypes) {
    this.CustomWorkTypes = CWT;
    CWT.DashboardUi = this;
    CWT.init();
  }

  private _registerAll() {
    this._registerMenu(new Menu());
  }

  private _registerMenu(Menu: Menu) {
    this.Menu = Menu;
    Menu.DashboardUi = this;
    Menu.init();
  }

  public closeMenu() {
    this.Menu.closeMobile();
  }
  public closeCWT() {
    this.CustomWorkTypes.closeCategoryContainerMobile();
  }
  public closeSnapshot() {
    this.Snapshot.hide();
  }
}
