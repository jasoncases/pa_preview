import { RuntimeConfigurationObject } from '../../Lib/Lib.js'
import { Store } from './Store.js'
import { Get } from './Get.js'
import { Put } from './Put.js'
import { Delete } from './Delete.js'
import { Secure } from './Secure.js'

export interface FetchOptions {
    html?: string
    open?: boolean
}

export class Fetch {
    route: string
    data: RuntimeConfigurationObject
    options: RequestInit
    html: string
    open: boolean

    public constructor(
        route: string,
        data: RuntimeConfigurationObject = {},
        options?: RequestInit,
        format: FetchOptions = { html: null, open: null },
    ) {
        this.route = route
        this.data = data
        this.options = options
        this.html = format.html || 'JSON'
        this.open = format.open || false
    }

    public static async get(
        route: string,
        data: RuntimeConfigurationObject = {},
        format: FetchOptions = { html: null, open: null },
    ) {
        return Fetch.run('get', route, data, null, format)
    }

    public static async store(
        route: string,
        data: RuntimeConfigurationObject = {},
        options?: RequestInit,
        format?: FetchOptions,
    ) {
        return Fetch.run('store', route, data, options, format)
    }
    public static async update(
        route: string,
        data: RuntimeConfigurationObject = {},
        options?: RequestInit,
        format?: FetchOptions,
    ) {
        return Fetch.run('put', route, data, options, format)
    }
    public static async destroy(
        route: string,
        data: RuntimeConfigurationObject = {},
        options?: RequestInit,
        format?: FetchOptions,
    ) {
        return Fetch.run('delete', route, data, options, format)
    }

    public static async secure(
        route: string,
        data: RuntimeConfigurationObject = {},
        options?: RequestInit,
        format?: FetchOptions,
    ) {
        return Fetch.run('secure', route, data, options, format)
    }

    private static async run(
        type: string,
        route: string,
        data: RuntimeConfigurationObject,
        options?: RequestInit,
        format?: FetchOptions,
    ) {
        const newFetch = new Fetch(route, data, options, format)
        return newFetch.create(type).run()
    }

    private create(type: string) {
        switch (type.toLowerCase()) {
            case 'store':
                return new Store(
                    this.route,
                    this.data,
                    this.options,
                    this.html,
                    this.open,
                )
            case 'get':
                return new Get(
                    this.route,
                    this.data,
                    this.options,
                    this.html,
                    this.open,
                )
            case 'put':
                return new Put(
                    this.route,
                    this.data,
                    this.options,
                    this.html,
                    this.open,
                )
            case 'delete':
                return new Delete(
                    this.route,
                    this.data,
                    this.options,
                    this.html,
                    this.open,
                )
            case 'secure':
                return new Secure(
                    this.route,
                    this.data,
                    this.options,
                    this.html,
                    this.open,
                )
        }
    }
}
