import { ExtendError } from "../../ExtendError.js"

export class RemoveAssignees extends ExtendError {
  public message = 'Error removing assignee from selected task'

  protected _runaswell() {
  }
}