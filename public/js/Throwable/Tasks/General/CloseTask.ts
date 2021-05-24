import { ExtendError } from '../../ExtendError.js'

export class CloseTask extends ExtendError {
  public message = 'Error closing task'

  protected _runaswell() {
  }
}