import { DropdownComponent } from '../../../Component/DropdownComponent.js'
import { MultiSelect } from '../MultiSelect.js'
import { clamp, returnFetchResponse } from '../../../../Lib/Lib.js'
import { Component } from '../../../Component/Component.js'

export class Dropdown extends Component {
  Parent: MultiSelect
  _selectables: Array<HTMLElement> = [];
  _selected: Array<any> = [];
  _isSelecting: boolean
  public constructor() {
    super()
  }

  public clearFocus() {
    this._element.style.zIndex = '-1'
    this._element.style.opacity = '0'
    this._element.blur()
    // this._element.style.outline = 'none'
  }

  public resetElement() {
    this._element.removeAttribute('style')
  }

  protected _registerAll() {
    this._registerElement()
    this._registerArrayOfSelectables()
    this._registerPresetValues()
  }

  private _registerPresetValues() { }

  protected _registerElement() {
    this._element = this._target.querySelector('#dropdown')
  }

  protected _initListeners() {
    this._element.addEventListener('mousedown', (event) =>
      this._mouseDown(event),
    )
    // this._element.addEventListener('keydown', event =>
    //   this._keyPress(<KeyboardEvent>event),
    // );
  }

  private _mouseDown(event: MouseEvent) {
    if (!this._targetIsValid(<HTMLElement>event.target)) return
    this.clearActiveElementState()
    this.Parent.setPointer(
      this._selectables.indexOf(<HTMLElement>event.target),
    )
    this._selectCurrentElement()
    this.Parent.setValue(this._selected.toString())
  }

  private _targetIsValid(el: HTMLElement) {
    if (this._validateTargetIsDisabled(el)) return false
    return true
  }

  private _validateTargetIsDisabled(el) {
    const str = 'disabled'
    const cl = Array.from(el.classList)
    return cl.indexOf(str) >= 0
  }

  private _registerArrayOfSelectables() {
    this._selectables = Array.from(this._element.querySelectorAll('li'))
  }

  public escape() {
    if (this._isSelecting) {
      this._deactivateIsSelecting()
    }
  }

  public return() {
    const isElement = this._element === document.activeElement
    const isTarget = this.Parent.getTarget() === document.activeElement
    const isParent = this.Parent.getElement() === document.activeElement
    if (!isTarget && !isElement && !isParent) return

    if (this._isSelecting) {
      this._selectCurrentElement()
    } else {
      this._activateIsSelecting()
    }
  }

  public arrowUp() {
    if (!this._isSelecting) {
      this._activateIsSelecting()
    }
    let currPointer = this.Parent.getPointer()
    const pointer = clamp(--currPointer, 0, this._selectables.length - 1)
    this.Parent.setPointer(pointer)
    this._updateMarkedActive()
  }

  public arrowDown() {
    if (!this._isSelecting) {
      this._activateIsSelecting()
    }
    let currPointer = this.Parent.getPointer()
    const pointer = clamp(++currPointer, 0, this._selectables.length - 1)
    this.Parent.setPointer(pointer)
    this._updateMarkedActive()
  }

  private _updateMarkedActive() {
    this._selectables.forEach((node) => {
      if (this._selectables.indexOf(node) !== this.Parent.getPointer()) {
        node.classList.remove('hover')
      } else {
        node.classList.add('hover')
        this._adjustScroll(node)
      }
    })
  }

  private _adjustScroll(node: HTMLElement) {
    const elementBottom = node.offsetHeight + node.offsetTop
    const dropdownHeight = this._element.offsetHeight
    const diff = Math.abs(dropdownHeight - elementBottom)
    if (elementBottom > dropdownHeight) {
      this._element.scrollTo({
        top: diff,
        behavior: 'smooth',
      })
    } else {
      this._element.scrollTo({
        top: 0,
        behavior: 'smooth',
      })
    }
  }

  public _selectCurrentElement() {
    try {
      this._toggleCurrentElementSelected()
      this._toggleCurrentElementValue()
      this.Parent.setValue(this._selected.toString())
      this._updateDisplay()
    } catch (e) {
      console.error(e)
      console.warn(this)
    }
  }

  private _activateIsSelecting() {
    this._isSelecting = true
    this.Parent.open()
    this._updateMarkedActive()
  }

  private _deactivateIsSelecting() {
    this._isSelecting = false
    this.Parent.close()
  }

  private _toggleCurrentElementSelected() {
    const currEl = this._selectables[this.Parent.getPointer()]
    currEl.classList.toggle('selected')
  }

  private _toggleCurrentElementValue() {
    //
    const currEl = this._selectables[this.Parent.getPointer()]
    const value = currEl.getAttribute('data-value')
    if (this._isSelected(currEl)) {
      this._selected.push(value)
    } else {
      this._selected = this._selected.filter((val) => {
        return val != value
      })
    }
  }

  private _isSelected(el: HTMLElement) {
    return Array.from(el.classList).indexOf('selected') >= 0
  }

  public getSelectedText() {
    const c = []
    this._selectables.forEach((node) => {
      if (this._isSelected(node)) {
        const text = node.innerText
        c.push(node.innerText.trim())
      }
    })
    return c
  }

  private _updateDisplay() {
    this.Parent.updateDisplay(this.getSelectedText())
  }

  public getIsSelecting() {
    return this._isSelecting
  }

  public clearActiveElementState() {
    this._selectables.forEach((node) => {
      node.classList.remove('hover')
    })
  }

  public setInitialValues(initialValues: Array<any>) {
    if (initialValues === null) return
    Object.keys(this._selectables).forEach((key) => {
      const node = this._selectables[key]
      const foundNum = initialValues.indexOf(
        parseInt(node.getAttribute('data-value')),
      )
      const foundStr = initialValues.indexOf(node.getAttribute('data-value'))
      if (foundNum >= 0 || foundStr >= 0) {
        this.Parent.setPointer(parseInt(key))
        this._selectCurrentElement()
      }
    })

    this.Parent.setPointer(0)
  }

  public reset() {
    this._selected.length = 0
    this._unselectAll()
    this._resetPointer()
  }

  private _resetPointer() {
    this.Parent.setPointer(0)
  }

  private _unselectAll() {
    this._selectables.forEach((el) => {
      el.classList.remove('selected')
    })
  }
}
