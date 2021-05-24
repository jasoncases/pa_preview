import { Component } from '../../../System/Components/Component/Component.js'
import { Actions } from '../../../Tasks/Filter/src/Components/Actions/Actions.js'
import { Ticket } from '../../../Tasks/Filter/src/Components/Record/TicketRecord.js'
import { FilterRequestObject } from '../../../Tasks/Filter/src/Filter.js'
import { TicketRecord } from '../../../Tasks/Filter/src/Interface/Lib.js'
import { User } from '../../../User/src/User.js'

export class Tasks extends Component {
  _id: string = 'ticket-filter-container'
  _req: FilterRequestObject

  rows: Array<Ticket> = []

  protected _registerAll() {
    try {
      this._registerElementById()
      User.registerListener(this)
      console.warn('Tasks.....testing in here')
    } catch (e) { console.error('Tasks error: ', e) }
  }

  public update() {
    this._setTaskRequest()
    this._loadTasks()
  }

  private _setTaskRequest() {
    this._req = <FilterRequestObject>{
      author: [],
      categoryId: [],
      keyword: null,
      order: null,
      page: 1,
      priorityId: [],
      recordsPerPage: 40,
      replyPriority: null,
      statusId: 1,
      assigneeId: [User.get('employeeId')],
      startId: null,
      range: null,
      perpetualStatus: null,
    }
  }

  private _loadTasks() {
    Actions.loadFilterTickets(this._req).then((res) => {
      console.log('this._req:', this._req)
      if (res.status === 'success') {
        console.log('Task - res:', res)
        this._element.innerHTML = ''
        const records = <Array<TicketRecord>>res.data.result
        records.forEach((rec) => {
          this.rows.push(this._renderRecord(rec))
        })
      }
    })
  }

  private _renderRecord(record: TicketRecord) {
    const ticket = new Ticket(record)
    ticket.registerTargetElementByParent(this._element)
    ticket.init()
    return ticket
  }
}
