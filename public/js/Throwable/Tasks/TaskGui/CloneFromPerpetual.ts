import { ExtendError } from "../../ExtendError.js"

export class CloneFromPerpetual extends ExtendError {
  public message = 'Error creating an ExeTask object from a Perpetual Task'

  protected _runaswell() {
  }
}