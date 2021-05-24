import { ExtendError } from "../../ExtendError.js"

export class PromoteToPerpetual extends ExtendError {
  public message = 'Promoting ExeTask to Perpetual state'

  protected _runaswell() {
  }
}