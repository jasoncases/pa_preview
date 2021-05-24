import {
    handleFetchResponse,
    FetchResponseInterface,
    RuntimeConfigurationObject,
} from '../../Lib/Lib.js'

export class Session {
    private static instance: Session
    public session: RuntimeConfigurationObject = {};
    private listeners: RuntimeConfigurationObject = {};
    private constructor() {
        this._init()
    }

    public static getInstance(): Session {
        if (!Session.instance) {
            Session.instance = new Session()
        }
        return Session.instance
    }

    private _init() {
        this._load()
    }

    private async _load() {
        this._fetchSession().then((response) => {
            if (response.status === 'success') {
                this._parse(response.data)
                this._updateListeners()
            }
        })
    }

    private _parse(data) {
        this.session = { ...data }
    }

    public reload() {
        this._load()
    }

    private async _fetchSession(): Promise<FetchResponseInterface> {
        const request = await fetch('/api/sess')
        return await request.text().then((data) => {
            return handleFetchResponse(request, data)
        })
    }

    public registerListener(component, key = 'all') {
        console.log('typeof this.listeners[key]:', typeof this.listeners[key])
        if (typeof this.listeners[key] === 'undefined') {
            this.listeners[key] = []
        }
        this.listeners[key].push(component)
    }

    private _updateListeners() {
        Object.keys(this.listeners).forEach((key) => {
            this.listeners[key].forEach((component) => {
                component.update()
            })
        })
    }

    public store(key: string, data: RuntimeConfigurationObject) {
        this._storeDataToSession(key, data)
    }

    public get(key: string) {
        if (this.session.hasOwnProperty(key)) {
            return this.session[key]
        } else {
            return null
        }
    }
    private async _storeDataToSession(
        key: string,
        data: RuntimeConfigurationObject,
    ) {
        const request = await fetch('/api/sess', {
            method: 'POST',
            mode: 'cors',
            headers: {},
            body: `key=${key}&data=${JSON.stringify(data)}`,
        })
        return await request.text().then((response) => {
            return handleFetchResponse(request, response)
        })
    }
}
