import { Request } from './Request.js'
import { RuntimeConfigurationObject } from '../../Lib/Lib.js'

export class Get extends Request {
    method: string = 'GET';
    public constructor(
        route: string,
        data: RuntimeConfigurationObject,
        options?: RequestInit,
        html: string = 'JSON',
        open: boolean = false,
    ) {
        super(route, data, options, html, open)
        this.options = { credentials: <RequestCredentials>'include' }
    }

    protected _finalize() {
        if (this.data) {
            this.route += this._createQueryStringData()
        }
    }

    protected _createQueryStringData() {
        const append = []
        Object.keys(this.data).forEach((key) => {
            let v = this.data[key]
            if (typeof this.data[key] === 'object') {
                v = JSON.stringify(v)
            }
            append.push(`${key}=${v}`)
        })
        return `?${append.join('&')}`
    }
}
