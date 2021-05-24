import { Component } from "../Component/Component.js";
import { applyCSSClasses, removeCSSClasses } from "../../Lib/Lib.js";

export class Slider extends Component {
    //props
    _id: string = `ui:alertSlider`;
    level: string;
    content: string;
    msTimer: number;
    timeout: any;
    removeTimer: number;
    removeDelay: number = 1500;
    validLevels: Array<string> = ["info", "caution", "danger", "success"];
    // CSS class info
    _classList: Array<string> = ["slide-alert"];
    _slideClass: Array<string> = ["slide-action"];

    static __instance: Slider;

    // node
    _content: HTMLElement;

    public buildConstructor(level: string, content: string, msTimer: number) {
        this.level = level;
        this.content = content;
        this.msTimer = msTimer;
        this.removeTimer = msTimer + this.removeDelay;
        this._init();
    }

    public static getInstance() {
        if (!Slider.__instance) {
            Slider.__instance = new Slider();
        }
        return Slider.__instance;
    }

    private _replaceDeprecatedLevel(level: string) {
        const deprecated: Array<string> = [];
    }

    private _validateLevel(level: string) {
        if (this.validLevels.indexOf(level) < 0) {
            throw `Error: Invalid level value provided. "${level}" given. Valid level values are "info", "caution", "danger", or "success"`;
        }
    }

    /**
     *
     * @param level string    - alert level, validated w/ try/catch
     * @param content string  - content message [can include HTML, i.e. links]
     * @param msTimer number  - how long the node stays active in [ms]
     */
    public static create(
        level: string,
        content: string,
        msTimer: number = 4000
    ) {
        const slider = Slider.getInstance();
        return slider.buildConstructor(level, content, msTimer);
    }

    protected _registerAll() {
        try {
            this._validateLevel(this.level);
            this._removeElement();
            this._registerElement();
            this._initListeners();
            this._registerSubElement();
            this._applyLevelToElement();
            this._animate();
        } catch (e) {
            console.error(e);
        }
    }

    protected _initListeners() {
        this._element.addEventListener("click", (e) =>
            this._mouseClickContainer(e)
        );
        this._element.addEventListener("touchstart", (e) => this._touch(e));
    }

    private _touch(event) {
        this._action(event);
    }
    protected _action(event: MouseEvent) {
        this._slideOut();
        setTimeout(() => {
            this._removeElement();
        }, 500);
    }

    private _insertAlertContentToContainer() {
        this._content.innerHTML = this.content;
    }

    private _registerSubElement() {
        this._createSubElement();
        this._insertAlertContentToContainer();
    }

    private _createSubElement() {
        this._content = this._element.querySelector('[id="ui:alertContent"]');
    }

    protected _applyLevelToElement() {
        applyCSSClasses(this._element, [`alert-${this.level}`]);
    }

    protected _registerElement() {
        this._element = this._createElement();
    }

    private _createElement() {
        const div = document.createElement("div");
        div.innerHTML = this._sliderInnerHTML();
        div.id = this._id;
        applyCSSClasses(div, this._classList);
        return div;
    }

    private _animate() {
        this._appendToBody();
        this._slideIn();
        this._removeFromBody();
    }

    private _appendToBody() {
        const body = document.querySelector('[id="wrapper"]');
        body.appendChild(this._element);
    }

    /**
     * Apply the slide animation classes to the element node. The msTimer
     * value tells the applyCSClasses method how long to keep the classes
     * active. It then removes the class.
     *
     * ? This is wrapped in a setTimeout w/ a micro-delay because if it
     * ? was all done in a linear manner, the slide-in animation would not
     * ? play. There may be another option, but this works for now.
     */
    private _slideIn() {
        setTimeout(() => {
            // applyCSSClasses(this._element, this._slideClass); // ! for debug
            applyCSSClasses(this._element, this._slideClass, this.msTimer);
        }, 25);
    }

    private _slideOut() {
        removeCSSClasses(this._element, this._slideClass);
    }

    /**
     * We want to remove the node from the body tag to make sure we're
     * clear to create another. These should not be overlapping often, so
     * a simple clear at instantiation should work.
     */
    private _removeFromBody() {
        // set the time out so it can be cleared when the element is removed
        this.timeout = setTimeout(() => {
            this._removeElement();
        }, this.removeTimer);
    }

    /**
     * Called at initialization to remove any existing elements so as to
     * negate conflicts. Rather than reference this._element, we broke out
     * to a regular DOM capture so this will work as a start and end
     * removal
     */
    protected _removeElement() {
        // clear timeout so it doesn't remove the next sequential element
        clearTimeout(this.timeout);
        const node = document.getElementById(this._id);
        if (node) {
            node.remove();
        }
    }

    /**
     * InnerHTML of the main alert node. Easier to do this than to create
     * a template tag and worry about cloning.
     */
    private _sliderInnerHTML() {
        return `<span class="alert-icon"></span>
              <span class="alert-content flex-col flex-center col-center w-80">
                <span class="flex-row flex-start col-center" id="ui:alertContent"></span>
              </span>`;
    }
}
