import { ExtendError } from "../../ExtendError.js"

export class ChangeAssignees extends ExtendError {
  public message = 'Error changing assignee data on selected task'

  protected _runaswell() {
  }
}