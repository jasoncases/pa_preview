import { ExtendError } from "./ExtendError.js"

export class ExtendErrorMissingKey extends ExtendError {
  public message = 'A Throwable instances was requested, but the key was not recognized'

  protected _runaswell(msg?: string) {
    console.error(`Requested Throwable key [${msg}] not found`)
  }
}