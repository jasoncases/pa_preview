import { Component } from '../../../System/Components/Component/Component.js'
import { Actions } from '../../../Codes/Filter/src/Components/Actions/Actions.js'
import { Ticket } from '../../../Codes/Filter/src/Components/Record/TicketRecord.js'
import { TicketRecord } from '../../../Codes/Filter/src/Interface/Lib.js'
import { User } from '../../../User/src/User.js'
import { FilterRequestObject } from '../../../Codes/Filter/src/Filter.js'

export class Codes extends Component {
    _id: string = 'codes-filter-container';
    _req: FilterRequestObject

    rows: Array<Ticket> = [];

    protected _registerAll() {
        this._registerElementById()
        User.registerListener(this)
    }

    public update() {
        console.warn('testing here')
        this._setTaskRequest()
        this._loadTasks()
    }

    private _setTaskRequest() {
        this._req = <FilterRequestObject>{
            author: [],
            categoryId: [],
            keyword: null,
            order: "DESC",
            page: 1,
            priorityId: [],
            recordsPerPage: 20,
            replyPriority: null,
            statusId: 1,
            assigneeId: [User.get('employeeId'), 'f10000', 'f20000'],
            lastRecordId: null,
            firstRecordId: null,
        }
    }


    private _loadTasks() {
        Actions.loadFilterTickets(this._req).then((res) => {
            if (res.status === 'success') {
                console.log('CDOES: res:', res)
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
