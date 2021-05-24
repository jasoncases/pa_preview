import { Component } from '../Component/Component.js'
import { applyCSSClasses } from '../../Lib/Lib.js'

interface TooltipOptions {
  vertPos?: string
  horizPos?: string
  color?: string
  icon?: string
}

export class Tooltip extends Component {
  _id: string = 'ui:tooltip:container'
  _classList: Array<string> = [
    'tooltip-container',
    'tooltip-bottom',
    'tooltip-right',
    'tooltip-info',
  ]
  _display: HTMLDivElement

  message: string
  options: TooltipOptions

  public constructor(message: string, options?: TooltipOptions) {
    super()
    this.message = message
    this.options = options
    this._init()
  }

  protected _registerAll() {
    this._registerElement()
    this._registerSubComponents()
    this._setMessageDisplay()
    this._setOptions()
  }

  private _setOptions() {}

  private _setMessageDisplay() {
    this._display.innerText = this.message
  }

  private _registerSubComponents() {
    this._display = <HTMLDivElement>(
      this._element.querySelector('[id="ui:tooltip:message"]')
    )
  }
  protected _registerElement() {
    this._element = this._createElement()
  }

  private _createElement() {
    const div = document.createElement('div')
    div.innerHTML = this._elementInnerHTML()
    div.id = this._id
    applyCSSClasses(div, this._classList)
    return div
  }

  private _elementInnerHTML() {
    return `<div class="pointer"></div>
            <div class="tooltip-icon"></div>
            <div class="tooltip-message" id="ui:tooltip:message"></div>`
  }

  /**
   * Methods to adjust the location, color, and icon of the tooltip.
   * It will be possible to send an options object through the constructor
   * as well, or explicitly state via public methods
   * @param cl
   */

  private _addClass(cl: string) {
    this._element.classList.add(cl)
  }
  private _removeClass(cl: string) {
    this._element.classList.remove(cl)
  }
  private _setLocation(loc: string) {
    this._addClass(`tooltip-${loc}`)
  }
  private _setMetaColor(color: string) {
    this._addClass(`tooltip-${color}`)
  }
  private _setIcon(icon: string) {
    this._addClass(`tooltip-${icon}`)
  }

  public setTop() {
    this._setLocation('top')
    this._removeClass('tooltip-bottom')
  }

  public setBottom() {
    this._setLocation('bottom')
    this._removeClass('tooltip-top')
  }

  public setLeft() {
    this._setLocation('left')
    this._removeClass('tooltip-right')
    this._removeClass('tooltip-center')
  }

  public setRight() {
    this._setLocation('right')
    this._removeClass('tooltip-left')
    this._removeClass('tooltip-center')
  }

  public setCenter() {
    this._setLocation('center')
    this._removeClass('tooltip-left')
    this._removeClass('tooltip-right')
  }

  public setRed() {
    this._setMetaColor('red')
    this._removeClass('tooltip-green')
    this._removeClass('tooltip-blue')
  }

  public setGreen() {
    this._setMetaColor('green')
    this._removeClass('tooltip-red')
    this._removeClass('tooltip-blue')
  }

  public setBlue() {
    this._setMetaColor('blue')
    this._removeClass('tooltip-green')
    this._removeClass('tooltip-red')
  }

  public setSuccess() {
    this._setIcon('success')
    this._removeClass('tooltip-info')
    this._removeClass('tooltip-caution')
    this._removeClass('tooltip-danger')
  }

  public setInfo() {
    this._setIcon('info')
    this._removeClass('tooltip-caution')
    this._removeClass('tooltip-success')
    this._removeClass('tooltip-danger')
  }

  public setCaution() {
    this._setIcon('caution')
    this._removeClass('tooltip-info')
    this._removeClass('tooltip-success')
    this._removeClass('tooltip-danger')
  }

  public setDanger() {
    this._setIcon('danger')
    this._removeClass('tooltip-info')
    this._removeClass('tooltip-caution')
    this._removeClass('tooltip-success')
  }

  public attachToTarget(el: HTMLElement) {
    console.log('el:', el)
    this._target = el
    this._target.style.position = 'relative'
    this._target.appendChild(this._element)
    this._setTargetListener()
  }

  public clear() {
    this._element.remove()
  }

  public setMessage(message: string) {
    this._display.innerText = message
  }

  public hide() {
    this._element.style.display = 'none'
  }
  public show() {
    this._element.removeAttribute('style')
  }

  private _setTargetListener() {
    this._target.addEventListener('focus', (event) =>
      this._targetIsFocus(event)
    )
  }

  private _targetIsFocus(event: Event) {
    console.log('event:', event)
    this.hide()
  }
}
