import { User } from '../../../../User/src/User.js'
import { isJSON } from '../../../../Schedule/src/Components/System/Lib.js'
import { Timeline } from './Timeline.js'
import { Fetch } from '../../../../System/Components/Fetch/Fetch.js'
import { FetchResponseInterface } from '../../../../System/Lib/Lib.js'

export class TimelineController {
    User: User

    _parent: HTMLElement

    public constructor() {
        //
        this._init()
    }

    private _init() {
        this._registerAll()
    }

    private _registerAll() {
        this._registerParent()
        this._registerAsListenerInUser()
    }

    private _registerParent() {
        this._parent = document.getElementById('timeline-container')
    }

    private _registerAsListenerInUser() {
        User.registerListener(this)
    }

    public update() {
        this._load()
    }

    private async _load() {
        return Fetch.get(`/timeline/${User.get('employeeId')}`)
            .then(r => this._handleResponse(r))
    }

    private _handleResponse(r: FetchResponseInterface) {
        if (r.status !== 'success') {
            return this._runOnFailure(r)
        }
        return this._runOnSuccess(r)
    }

    private _runOnFailure(r: FetchResponseInterface) { }

    private _runOnSuccess(r: FetchResponseInterface) {
        this._renderTimelines(r.data)
    }

    private _renderTimelines(data: any) {
        this._clearTimelines()
        Object.keys(data).forEach((key) => {
            const curr = data[key]
            new Timeline(data[key], User.get('employeeId'))
        })
    }

    private _clearTimelines() {
        this._parent.innerHTML = ''
    }
}
