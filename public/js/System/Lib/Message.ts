export class Messages {
  message: object = {
    task: {
      regenerate: 'Task regenerated from Perpetual template',
      saveError: 'Error saving new task - Please try again',
      statusComplete: 'Task set to completed',
      statusReopened: 'Task Reopened',
      priorityUpdate: 'Priority level updated successfully',
      closeErr: 'Error closing task',
      recurrenceSaved: 'Task recurrence data saved',
      recurrenceError: 'Error saving task recurrence data',
    },
    ticket: {},
    code: {}
  }

  public static message(module: string, key: string) {
    const msg = new Messages()
    return msg.message[module][key]
  }
}