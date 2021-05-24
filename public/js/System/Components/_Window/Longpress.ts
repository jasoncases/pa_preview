/**
 * Handler class for longpress vs regular mouse clicks
 * Provide target classes through lib function longpressClick(classObj)
 *
 * provided class needs (3) public methods:
 *  - getLongpressTarget() - returns the element the eventlisteners need
 *      to be applied to
 *  - longpress(event: any) - longpress event logic
 *  - click(event: any) - click event logic
 *  */

import {EventHandler} from './EventHandler.js';

export class LongpressEventHandler extends EventHandler {
  // length of mousedown to determine a longpress action
  protected pause: number = 500;
  protected trigger: boolean = false;

  /**
   *
   * @param classObj
   */
  protected _addListeners(classObj: any) {
    classObj
      .getEventTarget()
      .addEventListener('mousedown', (e) => this._mousedown(e, classObj));
    classObj
      .getEventTarget()
      .addEventListener('mouseup', (e) => this._mouseup(e, classObj));
  }

  /**
   *
   * @param event
   * @param classObj
   */
  private _mousedown(event: MouseEvent, classObj: any) {
    this.setTimer(event, classObj);
  }

  /**
   *
   * @param event
   * @param classObj
   */
  private _mouseup(event: any, classObj: any) {
    this.resetTimer();
    if (this.trigger) {
      this.classLongpress(event, classObj);
    } else {
      this.classClick(event, classObj);
    }
  }

  /**
   *
   * @param classObj
   */
  private classClick(event: MouseEvent, classObj: any) {
    if (typeof classObj.click !== 'function')
      throw 'Class.click not a function';
    return classObj.click(event);
  }

  /**
   *
   * @param classObj
   */
  private classLongpress(event: MouseEvent, classObj: any) {
    if (typeof classObj.longpress !== 'function')
      throw 'Class.longpress not a function';
    return classObj.longpress(event);
  }

  /**
   * fires class.longpressFire when the timer fires, without the client
   * actioning the mouseUp
   *
   * Uses: cause UI change to signify that the longpress up action is
   * primed, fire the actual event, etc
   *
   * @param classObj
   */
  private classLongpressFire(event: MouseEvent, classObj: any) {
    if (typeof classObj.longpressFire !== 'function')
      throw 'Class.longpressFire not a function';
    return classObj.longpressFire(event);
  }

  /**
   *
   */
  protected setTimer(event: any, classObj: any) {
    this.timer = setTimeout(() => {
      this.trigger = true;
      this.classLongpressFire(event, classObj);
    }, this.pause);
  }
}
