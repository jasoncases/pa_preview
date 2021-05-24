import { RuntimeConfigurationObject, applyCSSClasses } from '../../Lib/Lib.js'
import Creator from '../Creator/Creator.js'

interface ButtonInterface {
  _config: RuntimeConfigurationObject
  _parent: HTMLElement
  _element: HTMLButtonElement
  Creator: Creator
}

/**
 * Button Component
 *
 * Nothing to pass to parent constructor, everything can be set in
 * the constructor of the child.
 *
 * Use _extendElement method to modify the button element, add classLists,
 * setAttributes, etc etc
 */
export class Button implements ButtonInterface {
  _config: RuntimeConfigurationObject
  _parent: HTMLElement
  _element: HTMLButtonElement

  ButtonContainer: any
  Creator: Creator

  public constructor() { }

  /**
   *
   */
  protected _init() {
    this._registerAll()
    this._appendElement()
    this._initListeners()
  }

  /**
   * Register container to set class props/references
   */
  protected _registerAll() {
    this._registerCreator(new Creator())
    this._registerParent()
    this._registerElement()
  }

  /**
   * Register Creator Class
   *
   * @param Creator
   */
  protected _registerCreator(Creator: Creator) {
    this.Creator = Creator
  }

  /**
   * Register parent target element
   *
   */
  protected _registerParent() { }

  /**
   * Register target element, via Creator
   *
   */
  protected _registerElement() {
    this._element = this.Creator.createElement('button', {})
    this._extendElement()
  }

  /**
   *
   */
  protected _extendElement() { }

  /**
   * Assign classlist to the element
   *
   * @param classListArray
   */
  protected _registerClassList(classListArray: Array<string>) {
    applyCSSClasses(this._element, classListArray)
  }

  /**
   * Attach element to the target parent
   *
   */
  protected _appendElement() {
    this._parent.appendChild(this._element)
  }

  /**
   * Event Listener Container method
   */
  protected _initListeners() {
    this._element.addEventListener('click', (event) =>
      this._mouseClickContainer(event),
    )
    this._element.addEventListener('touchstart', (event) =>
      this._mouseClickContainer(<any>event),
    )
  }

  /**
   * Mouse action container method
   * @param event
   */
  protected _mouseClickContainer(event: MouseEvent) {
    //
    this._action(event)
  }

  protected _action(event: MouseEvent) { }
  /**
   * Method to set button state to disabled
   *
   */
  public setDisabled(inactiveClassArray: Array<string> = []) {
    if (inactiveClassArray.length > 0) {
      this._registerClassList(inactiveClassArray)
    }
    this._element.disabled = true
  }
}
