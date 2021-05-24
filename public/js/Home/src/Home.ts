import { Codes } from "./Components/Codes.js";
import { Tasks } from "./Components/Tasks.js";

export class Home {
    public constructor() {
        this._init();
    }
    private _init() {
        this._registerAll();
    }

    private _registerAll() {
        console.log("Home called.....");

        this._registerTasks(new Tasks());
        this._registerCodes(new Codes());
    }

    private _registerTasks(Tasks: Tasks) {
        Tasks.init();
        return Tasks;
    }
    private _registerCodes(Codes: Codes) {
        Codes.init();
        return Codes;
    }
}
