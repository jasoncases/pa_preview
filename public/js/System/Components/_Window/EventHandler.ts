export class EventHandler {
  protected flag: boolean = false;
  protected timer: any
  protected pause: number
  protected trigger: boolean

  public add(classObj: any) {
    try {
      this._initNewClass(classObj)
    } catch (e) {
      console.error(`EventHandler Error: ${e}`)
    }
  }

  protected _initNewClass(classObj: any) {
    if (typeof classObj.getEventTarget !== 'function') {
      console.log('its function...')
      throw `Class ${classObj.prototype.name ?? '[anon]'}.getEventTarget not a function`
    }
    if (!classObj.getEventTarget()) {
      console.log('its function...')
      throw `Class ${classObj.prototype.name ?? '[anon]'} target element not found`
    }
    this._addListeners(classObj)
  }

  protected _addListeners(classObj: any) { }

  protected resetTimer() {
    this.timer = null
    clearTimeout(this.timer)
  }

  protected setTimer(event: any, classObj: any) { }
}
