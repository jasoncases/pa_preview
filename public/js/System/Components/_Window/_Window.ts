import { eventPath, isAncestor } from '../../Lib/Lib.js'
import { DoubleClick } from './DoubleClick.js'
import { LongpressEventHandler } from './Longpress.js'

export class _Window {
  static __instance: _Window

  Longpress: LongpressEventHandler
  DoubleClick: DoubleClick

  outsideClickElements: Array<any> = []

  longpressClickElements: Array<any> = []

  private constructor() {
    this._init()
    this._registerLongpress(new LongpressEventHandler())
    this._registerDoubleClick(new DoubleClick())
  }

  private _registerDoubleClick(DoubleClick: DoubleClick) {
    this.DoubleClick = DoubleClick
  }
  private _registerLongpress(LongpressEventHandler: LongpressEventHandler) {
    this.Longpress = LongpressEventHandler
  }

  public static getInstance() {
    if (!_Window.__instance) {
      _Window.__instance = new _Window()
    }
    return _Window.__instance
  }

  /**
   * Push class to the container array. The class being added MUST HAVE
   * a method named getAncestor. getAncestor is the element you want the
   * click event compared against. In the case of a button group, the
   * parent element will be the desired Ancestor, because any dropdown
   * element clicks will not be decendents of the sister button element,
   * but they will be decendents of the apex group container element.
   *
   *    <div> <-- ancestor target
   *        <button>
   *        <ul -dropdown-menu>
   *
   *
   * @param classObj
   */
  public addOutsideClickElement(classObj: any) {
    this.outsideClickElements.push(classObj)
  }

  public addLongpressClick(classObj: any) {
    this.Longpress.add(classObj)
  }

  public addDoubleClick(classObj: any) {
    this.DoubleClick.add(classObj)
  }

  /**
   * initialize the window event handler
   */
  private _init() {
    window.onmousedown = (e) => {
      if (e.target.hasAttribute('data-ignoreOutsideClick')) return
      this.outsideClickElements.forEach((cls) => {
        this._selectInput(e, cls)
      })
    }
    window.addEventListener('touchstart', (e) => {
      if ((<HTMLElement>e.target).hasAttribute('data-ignoreOutsideClick')) return
      this.outsideClickElements.forEach((cls) => {
        this._selectInput(e, cls)
      })
    })
  }

  /**
   * In some cases, both events will fire. If touches exists, then it
   * will only call the touch event, otherwise it will only call the
   * click event
   *
   * @param event
   * @param cls
   */
  private _selectInput(event: any, cls) {
    try {
      if (event.touches) {
        this._performOutsideTouch(event, cls)
      } else {
        this._performOutsideClick(event, cls)
      }
    } catch (e) {
      console.error(e)
    }
  }

  private _performOutsideTouch(event, cls) {
    this._processInput(event, cls)
  }

  private _performOutsideClick(event, cls) {
    this._processInput(event, cls)
  }

  /**
   * Primary action
   *
   * @param event
   * @param cls
   */
  private _processInput(event, cls) {
    // check if the event.target element is in the cls exemptions
    if (this._targetIsExempt(event, cls)) return
    // class given must have getAncestor method
    if (typeof cls.getAncestor !== 'function')
      throw `${cls.prototype.name}.getAncestor not found`
    // isAncestor may be too ambiguous, but when the click happens, a
    // path from the event.target to the document top level is generated
    // (by default in Chrome, through a function in Safari), and if the
    // provided element is not in that path, isAncester returns FALSE
    if (isAncestor(event, cls.getAncestor())) return

    // last check to ensure that given class has a close method
    if (typeof cls.close !== 'function')
      throw `${cls.prototype.name}.close not found`
    cls.close(event)
  }

  /**
   * Checks for exemptions, i.e. items outside of the element's path
   * that do NOT trigger the close event
   *
   * ! Be careful of pointer events in child elements
   *
   * @param event
   * @param cls
   */
  private _targetIsExempt(event, cls) {
    if (typeof cls.outsideClickExempt !== 'function') return
    const exemptions = cls.outsideClickExempt()
    return exemptions.some((ext) => {
      if (event.target.classList.contains(ext)) return true
      if (event.target.id === ext) return true
      const path = eventPath(event)
      if (!path) return
      if (
        path.some((element) => {
          if (typeof element.classList === 'undefined') return
          return element.id === ext || element.classList.contains(ext)
        })
      ) {
        return true
      }
    })
  }
}
