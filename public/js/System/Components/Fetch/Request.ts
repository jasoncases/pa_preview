import {
    RuntimeConfigurationObject,
    handleFetchResponse,
    _get,
} from '../../Lib/Lib.js'

export class Request {
    route: string
    data: RuntimeConfigurationObject
    options: RequestInit
    method: string
    mode: RequestMode = 'cors';
    bodyString: Array<string> = [];
    html: string
    open: boolean
    public constructor(
        route: string,
        data: RuntimeConfigurationObject,
        options?: RequestInit,
        html: string = 'JSON',
        open: boolean = false,
    ) {
        this.route = route
        this.data = data
        this.html = html
        this.open = open
    }

    /**
     * Made this public and static as there may be other places we need
     * it in the future outside of the Request object
     */
    public static getScrfToken(): string {
        return (<HTMLMetaElement>document.querySelector('meta[name="csrf-token"]')).content
    }


    public async run() {
        this._finalize()
        const request = await fetch(this.route, this.options)
        return await request.text().then((response) => {
            return handleFetchResponse(request, response, this.html, this.open)
        })
    }

    protected _finalize() { }

    protected _setOptions(options: RequestInit) {
        return {
            method: this.method,
            mode: this._setMode(options),
            headers: this._setHeaders(options),
            credentials: <RequestCredentials>'include',
            body: this._setBody(),
        }
    }

    protected _setMode(options: RequestInit) {
        return typeof options === 'undefined' ? this.mode : options.mode
    }

    protected _setHeaders(options: RequestInit) {
        return typeof options === 'undefined'
            ? this._setDefaultHeaders()
            : options.headers
    }

    protected _setBody() {
        const c = []
        Object.keys(this.data).forEach((key) => {
            c.push(this._buildBodyString(key, this.data[key]))
        })
        return c.join('&')
    }

    protected _buildBodyString(key: string, value: any) {
        if (value === null) return
        if (typeof value === 'object') {
            value = JSON.stringify(value)
        }
        return `${key}=${encodeURIComponent(value)}`
    }

    protected _setDefaultHeaders() {
        return {
            Accept: 'application/json',
            'Content-type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': Request.getScrfToken()
        }
    }
}
