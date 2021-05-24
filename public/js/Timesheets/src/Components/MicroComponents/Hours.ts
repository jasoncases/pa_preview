export class Hours {
  target: string
  value: string

  public constructor(target: string, value: string) {
    this.target = target
    this.value = value
  }
  public static display(target: string, value: string) {
    const hr = new Hours(target, value)
    return hr.draw()
  }

  public draw() {
    if (this._exists(this.target)) {
      this._element(this.target).innerText = this.value
    }
  }

  private _exists(target) {
    return this._element(target)
  }

  private _element(target) {
    return document.getElementById(target)
  }

}
