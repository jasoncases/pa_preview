import { Component } from "../../System/Components/Component/Component.js";
import { Keypad } from "./Components/Keypad/Keypad.js";
import { Code } from "./Components/Code/Code.js";
import { BlipGroup } from "./Components/Blips/BlipGroup.js";
import { Action } from "./Components/Action/Action.js";
import { Action as SystemAction } from "../../System/Proaction/Actions/Action.js";
import { Response } from "../../System/Components/Response/Response.js";
import { FetchResponseInterface, isJSON } from "../../System/Lib/Lib.js";
import { Client } from "../../Client/Client.js";
import { Session } from "../../System/Components/Session/Session.js";
import { LocalStorage } from "../../System/Components/LocalStorage/LocalStorage.js";

interface LoginDataResponse {}
interface PinpadConfig {
    url: string;
    redirectDelay: number;
    codeLength: number;
}
export class Pinpad extends Component {
    // Component pointers:
    Keypad: Keypad;
    Code: Code;
    BlipGroup: BlipGroup;
    Client: Client;
    Session: Session;

    // Private props
    _state: string = null;
    _config: PinpadConfig;
    _url: string = "/landing";
    redirectUrl: string;
    lsKey: string = "client_pin_length";

    public constructor() {
        super();
        this._config = {
            url: "/landing",
            redirectDelay: 1800,
            codeLength: 4,
        };
        this._init();
    }

    // --
    // * Registration Methods
    // --
    protected _registerAll() {
        this._registerSession(Session.getInstance());
        this._registerAsSessionListener();
        this._getConfigCodeLength();
        // this._registerClient(Client.getInstance());
    }

    private _runAfterConfig() {
        this._registerKeypad(new Keypad());
        this._registerCode(new Code());
        this._registerBlipGroup(new BlipGroup());
        this._registerRedirectUrl();
    }

    private _loadPinpadConfig() {
        SystemAction.getClientSetting("pin_length").then((res) => {
            if (res.status === "success") {
                LocalStorage.store(btoa(this.lsKey), res.data.value);
                this._config.codeLength = res.data.value;
                this._runAfterConfig();
            }
        });
    }

    private _getConfigCodeLength() {
        if (LocalStorage.get(btoa(this.lsKey))) {
            if (isNaN(LocalStorage.get(btoa(this.lsKey))))
                return this._loadPinpadConfig();
            this._config.codeLength = LocalStorage.get(btoa(this.lsKey));
            this._runAfterConfig();
        } else {
            this._loadPinpadConfig();
        }
    }

    private _registerAsSessionListener() {
        this.Session.registerListener(this, "REDIRECT");
    }

    public update() {
        this._registerRedirectUrl();
    }

    private _registerRedirectUrl() {
        console.log("SESSION AT LOGIN: ", this.Session);
        if (typeof this.Session.session.system.REDIRECT !== "undefined") {
            console.log(
                "Storing redirect from session value, ",
                this.Session.session.system
            );
            this.redirectUrl = this.Session.session.system.REDIRECT;
        } else {
            this.redirectUrl = this._config.url;
        }
    }

    private _registerSession(Session: Session) {
        this.Session = Session;
    }

    protected _registerClient(Client: Client) {
        this.Client = Client;
    }

    protected _registerKeypad(Keypad: Keypad) {
        this.Keypad = Keypad;
        Keypad.Pinpad = this;
        Keypad.init();
    }

    protected _registerCode(Code: Code) {
        this.Code = Code;
        Code.Pinpad = this;
        Code.setCodeLengthSetting(this._config.codeLength);
    }

    protected _registerBlipGroup(BlipGroup: BlipGroup) {
        this.BlipGroup = BlipGroup;
        BlipGroup.Pinpad = this;
        BlipGroup.setCodeLengthSetting(this._config.codeLength);
        BlipGroup.init();
    }

    /**
     *
     */
    public getCodeLength() {
        return this.Code.getLength();
    }

    /**
     *
     * @param code
     */
    public addCode(code: number) {
        this.Code.addCode(code);
        this.BlipGroup.render();
        if (this.getCodeLength() >= this._config.codeLength) {
            this.submit();
        }
    }

    /**
     *
     */
    public removeCode() {
        this.Code.removeCode();
        this.BlipGroup.render();
    }

    /**
     *
     */
    public submit() {
        if (this.getCodeLength() < 4) return;
        if (this._state) return;
        this._submit();
    }

    /**
     *
     * @param state
     */
    protected _setState(state: string) {
        this._state = state;
    }

    /**
     *
     */
    protected _submit() {
        this._setState("submitting");
        Action.submit(this.Code.encodedOutput()).then((res) => {
            return this._handleResponse(res);
        });
    }

    protected _runOnSuccess(res: FetchResponseInterface) {
        if (typeof res.data.forcedPasswordReset !== "undefined") {
            if (Boolean(res.data.forcedPasswordReset)) {
                return this._redirectToForcedPasswordReset();
            }
        } else {
            return this._redirect();
        }
    }

    private _redirectToForcedPasswordReset() {
        this.redirectUrl = "/users/pw_recovery";
        return this._redirect();
    }

    protected _runOnFailure(res: FetchResponseInterface) {
        return this.reset();
    }

    /**
     *
     */
    public reset() {
        this._setState(null);
        this.Code.clear();
        this.BlipGroup.error();
    }

    /**
     *
     */
    private _redirect() {
        console.warn(`Redirecting to ${this.redirectUrl} -`);
        setTimeout(() => {
            window.location.href = this.redirectUrl;
        }, this._config.redirectDelay);
    }
}
