import {Component} from '../../../../System/Components/Component/Component.js';
import {DropdownComponent} from '../../../../System/Components/Component/DropdownComponent.js';
import {get} from '../../../../System/Lib/Lib.js';
import {Ui} from '../Ui.js';

export class Menu extends Component {
  DashboardUi: Ui;
  mobile: HTMLElement;
  desktop: HTMLElement;

  // sub component
  mobileInner: HTMLElement;

  _mobileIsOpen: boolean = false;

  protected _registerAll() {
    this._registerMobileMenu();
    this._registerDesktopMenu();
    this._registerSubComponents();
  }

  protected _initListeners() {
    this.mobile.addEventListener('click', (event) => this._mobileClick(event));
  }

  private _mobileClick(event: MouseEvent) {
    this.toggleMobile();
  }

  private _registerSubComponents() {
    this.mobileInner = <HTMLElement>get('mobileMenuInner');
  }

  private _registerMobileMenu() {
    this.mobile = <HTMLElement>get('toggle-menu');
  }
  private _registerDesktopMenu() {
    this.desktop = <HTMLElement>get('sidebar');
  }

  public openMobile() {
    if (!this.mobile) return;
    if (this._mobileIsOpen) return;
    const mobileMenuInner = <HTMLElement>get('mobileMenuInner');
    mobileMenuInner.classList.remove('menu-collapse');
    this._mobileIsOpen = true;
    this.DashboardUi.closeCWT();
    this.DashboardUi.closeSnapshot();
  }

  public closeMobile() {
    if (!this.mobile) return;
    if (!this._mobileIsOpen) return;
    this.mobileInner.classList.add('menu-collapse');
    this._mobileIsOpen = false;
  }

  private toggleMobile() {
    if (this._mobileIsOpen) {
      this.closeMobile();
    } else {
      this.openMobile();
    }
  }
}
