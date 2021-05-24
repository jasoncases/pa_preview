import { ExtendError } from "../../ExtendError.js"

export class ElementNotFound extends ExtendError {
  public message = 'Element missing ------------------------'
  protected _runaswell(msg?: string) {
    console.error(`No Component._element with [${msg}] found`)
  }
}

