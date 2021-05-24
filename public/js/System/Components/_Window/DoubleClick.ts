import { EventHandler } from './EventHandler.js'

export class DoubleClick extends EventHandler {
  // max allowed pause between first mouseup and next mousedown
  protected pause: number = 250;
  protected trigger: boolean = true;

  /**
   *
   * @param classObj
   */
  protected _addListeners(classObj: any) {
    classObj
      .getEventTarget()
      .addEventListener('mousedown', (e) => this._mousedown(e, classObj))
    classObj
      .getEventTarget()
      .addEventListener('mouseup', (e) => this._mouseup(e, classObj))
    classObj
      .getEventTarget()
      .addEventListener('touchstart', (e) => this._mousedown(e, classObj))
    classObj
      .getEventTarget()
      .addEventListener('touchend', (e) => this._mouseup(e, classObj))
  }

  /**
   * On mousedown, set trigger to inverse this.timer boolean value
   * If the timer is set, then we've experienced one click cycle, so we
   * don't want the single click
   *
   * @param event
   * @param classObj
   */
  protected _mousedown(event: MouseEvent, classObj: any) {
    this.trigger = !Boolean(this.timer)
  }

  /**
   * mouseup is the initiating event. If trigger is true, we set
   * the timer event. If timer fires, classObj fires its click() method.
   * If mousedown is triggered again before the timer fires, the
   * trigger is set to false and the mouseup event causes the dblclick
   * event to fire
   *
   * @param event
   * @param classObj
   */
  protected _mouseup(event: MouseEvent, classObj: any) {
    if (this.trigger) {
      this.setTimer(event, classObj)
    } else {
      this.__doubleClick(event, classObj)
    }
  }

  protected __singleClick(event: MouseEvent, classObj: any) {
    if (!this.trigger) return
    this.resetTimer()
    if (typeof classObj.click === 'function')
      return classObj.click()
  }

  protected __doubleClick(event: MouseEvent, classObj: any) {
    this.resetTimer()
    if (typeof classObj.dblclick === 'function')
      return classObj.dblclick()
  }

  protected setTimer(event: MouseEvent, classObj: any) {
    this.timer = setTimeout(() => {
      this.__singleClick(event, classObj)
    }, this.pause)
  }
}
