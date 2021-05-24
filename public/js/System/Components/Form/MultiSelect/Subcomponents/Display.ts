import { Component } from '../../../Component/Component.js'
import { MultiSelect } from '../MultiSelect.js'
import { RuntimeConfigurationObject } from '../../../../Lib/Lib.js'

export class Display extends Component {
  Parent: MultiSelect
  _initial: RuntimeConfigurationObject = {}
  public constructor() {
    super()
  }

  protected _registerAll() {
    this._registerElement()
    this._registerOriginValues()
    this._updateElementWidth()
  }

  /**
   * This method is necessary to call when the element is intially
   * hidden, as the display element will have a 0px width value, until
   * it is no longer hidden, via display: none;
   *
   * Call this after the element is displayed. Suggestion is to create
   * a `firstOpen` boolean variable to prevent this being called too
   * many times. Hasn't been tested, but could cause some bugs.
   *
   */
  public resetOriginValues() {
    this._registerOriginValues()
    this._updateElementWidth()
  }

  private _registerOriginValues() {
    this._registerOriginWidth()
    this._initial['display'] = this._element.innerText
  }

  private _registerOriginWidth() {
    this._initial['width'] = this._element.offsetWidth
  }

  protected _registerElement() {
    this._element = this._target.querySelector('#display')
  }

  public updateWindow(selected: Array<string>) {
    /**
     * If the element is display: none, or contained by a display: none,
     * the initial width will be 0, because the element hasn't been
     * rendered yet, if it's zero, reset the origin values so we can
     * update the element to the proper size
     */
    if (this._initial.width == 0) this._registerOriginWidth()
    if (selected.length == 0) {
      this._element.innerText = this._initial.display
    } else {
      selected.sort(function (a, b) {
        a = a.toLowerCase()
        b = b.toLowerCase()
        if (a == b) return 0
        return a < b ? -1 : 1
      })
      const count = selected.length
      const selectedString = selected.join(', ')
      if (count !== 1) {
        this._element.innerText = `(${count} total) - ${selectedString}`
      } else {
        this._element.innerText = `${selectedString}`
      }
    }
    this._updateElementWidth()
  }

  /**
   *
   */
  private _updateElementWidth() {
    // guard clause will trigger when main element is display: none;
    // prevents a hard style value from being set. Allows the component
    // to reset itself via the resetOriginValues() public method
    if (this._initial.width === 0) return
    this._element.setAttribute('style', `width:${this._initial.width}px`)
    // console.log('Initial Width vs End Width', this._initial.width, 'px')
  }

  public getInnerText() {
    return this._element.innerText
  }

  public getOffsetWidth() {
    return this._element.offsetWidth
  }

  public reset() {
    this._element.innerText = this._initial.display
  }
}
