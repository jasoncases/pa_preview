import { DropdownComponent } from '../../Component/DropdownComponent.js'
import { MultiSelectors } from './MultiSelectors.js'
import { clamp, RuntimeConfigurationObject } from '../../../Lib/Lib.js'
import { Tooltip } from '../../Tooltip/Tooltip.js'
import { Input } from './Subcomponents/Input.js'
import { Display } from './Subcomponents/Display.js'
import { Dropdown } from './Subcomponents/Dropdown.js'

export class MultiSelect extends DropdownComponent {
  MultiSelectors: MultiSelectors = null;

  _isSelecting: boolean = false;
  _display: Display
  _dropdown: Dropdown
  _input: Input
  _pointer: number = 0;
  _values: Array<any> = [];
  _options: RuntimeConfigurationObject = {};
  _elementHover: boolean = false;
  _initial: RuntimeConfigurationObject = {};
  _tooltip: Tooltip
  _mobileCloser: HTMLButtonElement
  _isDisabled: boolean = false;
  closeActionCallback: any = null;

  public constructor(node: HTMLElement) {
    super()
    this._element = node
    this._init()
  }

  public setPointer(num: number) {
    this._pointer = num
  }

  public getPointer() {
    return this._pointer
  }

  public setValue(value: any) {
    this._input.setValue(value)
    this._extendSetFormElementValue()
  }

  public getInitialValue(key: string) {
    return this._initial[key]
  }

  protected _registerAll() {
    this._registerTargetElement()
    this._initTargetListener()
    this._getDisabledState()
  }

  public enable() {
    if (!this._isDisabled) return
    this._target.removeAttribute('disabled')
    this._getDisabledState()
  }

  public disable() {
    if (this._isDisabled) return
    this._target.setAttribute('disabled', '')
    this._getDisabledState()
  }

  public resetDisplayOriginValues() { }
  private _getDisabledState() {
    this._isDisabled = this._target.hasAttribute('disabled')
  }

  protected _extendListeners() {
    this._element.addEventListener('keydown', (event) =>
      this._keyPress(<KeyboardEvent>event),
    )
    this._target.addEventListener('mousedown', (event) =>
      this._fixStateError(event),
    )
    this._element.addEventListener('mouseenter', (event) =>
      this._mouseEnter(event),
    )
    this._element.addEventListener('mouseleave', (event) =>
      this._mouseLeave(event),
    )
    window.addEventListener('mousedown', (event) =>
      this._globalMouseDown(event),
    )
  }

  private _mouseEnter(event: MouseEvent) {
    if (this._isDisabled) return
    this._elementHover = true
  }

  private _mouseLeave(event: MouseEvent) {
    if (this._isDisabled) return
    this._elementHover = false
  }

  private _globalMouseDown(event: MouseEvent) {
    if (this._isDisabled) return
    if (!this._open) return
    if (this._elementHover) return
    this._setOpenState(false)
  }

  private _keyPress(event: KeyboardEvent) {
    if (this._isDisabled) return
    this._routeKeyPressEvent(event)
  }

  private _routeKeyPressEvent(event) {
    switch (event.keyCode) {
      case 13:
        return this._return()
      case 38:
        return this._arrowUp()
      case 40:
        return this._arrowDown()
      case 27:
        return this._escape()
      default:
        return false
    }
  }

  private _escape() {
    this._dropdown.escape()
  }

  private _return() {
    this._dropdown.return()
  }

  private _arrowUp() {
    this._dropdown.arrowUp()
  }

  private _arrowDown() {
    this._dropdown.arrowDown()
  }

  private _fixStateError(event: MouseEvent) {
    // if (this._isDisabled) return;
    // this._isSelecting = false;
    // this._setClosedDisplay();
    // console.log('fixstateerrorcallednowokay?');
  }

  protected _extendRegister() {
    this._registerSubComponents()
    this._registerOptions()
    this._registerInitialState()
    this._setInitialValues()
  }

  private _registerInitialState() { }

  private _registerOptions() {
    Object.keys(this._element.dataset).forEach((key) => {
      this._options[key] = this._element.dataset[key]
    })
    // going to snatch options to have display marquee, flash through, etc?
  }

  private _registerSubComponents() {
    this._registerDisplay()
    this._registerDropdownElements()
    this._registerHiddenFormElement()
    this._registerMobileCloser()
  }

  private _registerMobileCloser() {
    this._mobileCloser = this._element.querySelector('#closer')
    if (this._mobileCloser) {
      this._mobileCloser.addEventListener('touch', (event) => {
        this._isSelecting = false
        this.close()
      })
      this._mobileCloser.addEventListener('click', (event) => {
        this._isSelecting = false
        this.close()
      })
    }
  }

  public setDefaultDisplay(arr: Array<any>) {
    this._dropdown.setInitialValues(arr)
  }

  protected _setInitialValues() {
    this._input.registerPresets()
  }

  /**
   * _target value is the Button element, which can be disabled.
   *
   */
  protected _registerTargetElement() {
    this._target = <HTMLElement>this._element.firstElementChild
  }

  protected _setOpenDisplay() {
    if (this.MultiSelectors) {
      this.MultiSelectors.closeAll(this)
    }
    this._element.classList.add('open')
  }

  protected _setClosedDisplay() {
    if (this._dropdown.getIsSelecting()) return
    this._dropdown.clearFocus()
    console.log('switching to element focus')
    setTimeout(() => {
      this._element.focus()
      console.log('firing timeout')
      this._element.classList.remove('open')
      this._closeAction()
      this._dropdown.resetElement()
    }, 50)
  }

  public setCloseActionCallBack(cb: any) {
    if (typeof cb != 'function') return
    this.closeActionCallback = cb
  }

  protected _closeAction() {
    if (typeof this.closeActionCallback != 'function') return
    this.closeActionCallback()
  }

  /**
   * Allows an outside class to tell the MutliSelect component what to 
   * display. 
   * 
   * 
   * TODO - add another public method that feeds option values
   * 
   * @param selected Array of strings to be displayed
   * 
   * @returns void
   */
  public updateDisplay(selected: Array<string>) {
    this._display.updateWindow(selected)
    this._adjustTargetWidth()
  }

  private _adjustTargetWidth() {
    if (this._options.hasOwnProperty('fixed')) {
      // this._target.style.width = `${this._initial.width}px`;
      console.log('this._target:', this._target)
    }
  }

  public getOptions() {
    return this._options
  }

  protected _extendSetFormElementValue() { }

  /**
   *  ! SUBCOMPONENT REGISTRATION
   */

  private _registerHiddenFormElement() {
    this._input = this._createInput(new Input())
  }

  private _createInput(Input: Input) {
    Input.Parent = this
    Input.registerTargetElementByParent(this._element)
    Input.init()
    return Input
  }

  private _registerDisplay() {
    this._display = this._createDisplay(new Display())
  }

  private _createDisplay(Display: Display) {
    Display.Parent = this
    Display.registerTargetElementByParent(this._element)
    Display.init()
    return Display
  }

  private _registerDropdownElements() {
    this._dropdown = this._createDropdown(new Dropdown())
  }

  private _createDropdown(Dropdown: Dropdown) {
    Dropdown.Parent = this
    Dropdown.registerTargetElementByParent(this._element)
    Dropdown.init()
    return Dropdown
  }

  public reset() {
    this._resetAll()
  }

  private _resetAll() {
    this._dropdown.reset()
    this._input.reset()
    this._display.reset()
  }
}
