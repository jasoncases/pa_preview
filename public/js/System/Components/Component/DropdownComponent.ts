import { Component } from './Component.js'

export class DropdownComponent extends Component {
    _open: boolean = false

    public constructor() {
        super()
    }

    /**
     *
     * @param bool
     */
    protected _setOpenState(bool: boolean) {
        this._open = bool
        this._setDisplayState()
    }

    protected _runClosedCallback() { }
    protected _runOpenCallback() { }

    /**
     *
     */
    protected _setDisplayState() {
        return this._open ? this._setOpen() : this._setClosed()
    }

    protected _setClosed() {
        this._setClosedDisplay()
        this._runClosedCallback()
    }

    protected _setOpen() {
        this._setOpenDisplay()
        this._runOpenCallback()
    }

    /**
     *
     */
    protected _setOpenDisplay() { }

    /**
     *
     */
    protected _setClosedDisplay() { }

    /**
     *
     */
    public open() {
        if (this._open) return
        this._setOpenState(true)
    }

    /**
     *
     */
    public close(evt?: any) {
        if (!this._open) return
        this._setOpenState(false)
    }

    /**
     *
     */
    protected _initTargetListener() {
        this._target.addEventListener('click', (event) =>
            this._targetClickContainer(event)
        )
    }

    /**
     *
     * @param event
     */
    protected _targetClickContainer(event: MouseEvent) {
        this._setOpenState(!this._open)
    }

    protected _extendInit() {
        if (this._open) {
            this._setDisplayState()
        }
    }

    public toggleOpen() {
        this._open = !this._open
        this._setOpenState(this._open)
    }
}
