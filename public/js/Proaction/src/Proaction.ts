import { ProactionCache } from "./InterfaceLib.js";

export class Proaction {
    listeners: Array<any> = [];
    cache: object;
    eventSource: EventSource;

    private static __instance: Proaction;

    private constructor() {
        this._buildSource();
    }

    public static getInstance() {
        if (!Proaction.__instance) {
            Proaction.__instance = new Proaction();
        }
        return Proaction.__instance;
    }

    public static addListener(cls: any) {
        Proaction.getInstance()._add(cls);
    }

    private _add(cls: any) {
        this.listeners.push(cls);
    }

    private _updateListeners() {
        this.listeners.forEach((cls) => {
            if (typeof cls.update === "function") {
                if (typeof cls.getCacheKey !== "function") {
                    return cls.update(this.cache);
                }
                // console.log("cls: ", cls);
                return cls.update(this.cache[cls.getCacheKey()]);
            }
        });
    }

    private _buildSource() {
        this.eventSource = new EventSource(`/api/proaction`);
        const self = this;
        this.eventSource.onmessage = (e) => self._parse(e);
    }

    private _parse(e: MessageEvent) {
        this.cache = <ProactionCache>JSON.parse(e.data);
        this._updateListeners();
    }
}
