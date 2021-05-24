export class ExtendError extends Error {
  public message = "Generic error message"
  public constructor(message?: string) {
    super()
    this._errorExtension(message)
  }

  private _errorExtension(msg?: string) {
    this._runaswell(msg)
  }

  /**
   * Run as well is called at every ExtendError constructor to allow
   * the extension to perform logging and other necessary tasks on error
   * throws
   */
  protected _runaswell(msg?: string) { }
}