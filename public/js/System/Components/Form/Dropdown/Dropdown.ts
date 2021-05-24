import {applyCSSClasses, get} from '../../../Lib/Lib.js';
import {Component} from '../../Component/Component.js';
import {Search} from '../../Search/Search.js';

export class Dropdown extends Component {
  _dropdown: HTMLElement;
  _classList: Array<string> = [];
  _elementBounds: any;
  model: any;
  requestedValues: Array<string>;
  modelRestrictions: any;
  cb: any;
  mi: any;
  options: any = {};
  public constructor(
    id: string,
    model: any,
    requestedValues: Array<string>,
    modelRestrictions: any,
    cb: any = null,
    options: any = {},
  ) {
    super();
    this.model = model;
    this.modelRestrictions = modelRestrictions;
    this._id = id;
    this.requestedValues = requestedValues;
    this.cb = cb;
    this._initializeOptions(options);
    this._init();
  }

  private _initializeOptions(options: any = {}) {
    if (typeof options.onNoResults === 'function')
      this.options.onNoResults = options.onNoResults;

    if (typeof options.onResultsFound === 'function')
      this.options.onResultsFound = options.onResultsFound;
  }

  protected _registerAll() {
    this._registerElementById();
    this._registerDropdown();
  }

  private _getElementBounds() {
    this._elementBounds = this._element.getBoundingClientRect();
  }

  protected _modifyElement() {
    this._element.style.position = 'relative';
  }

  private _registerDropdown() {
    this._dropdown = this._createDropdown();
    (<HTMLElement>get('component-anchor')).appendChild(this._dropdown);
    this._setDropdownPosition();
  }

  private _createDropdown() {
    const ul = document.createElement('ul');
    ul.id = 'autodropdown';
    this._applyStyles(ul);
    applyCSSClasses(ul, this._classList);
    return ul;
  }

  private _applyStyles(el) {
    el.style.position = 'absolute';
    el.style.backgroundColor = 'white';
    el.style.border = '1px solid black';
    el.style.borderRadius = '5px';
    el.style.zIndex = '10000';
    el.style.marginTop = '-1px';
    el.style.display = 'none';
    el.style.fontSize = '14px';
    el.style.maxHeight = '250px';
    el.style.overflow = 'hidden';
    el.style.overflowY = 'auto';
  }

  protected _initListeners() {
    this._element.addEventListener('keyup', (event) => this._keyPress(event));
    const content = <HTMLElement>get('content');
    content.onscroll = (e) => {
      this._setDropdownPosition();
    };
  }

  private _keyPress(event: KeyboardEvent) {
    if (event.target !== this._element) return;
    const value = (<HTMLInputElement>event.target).value;
    if (value.length < 3) {
      this._hideDropdown();
      return;
    }
    Search.find(
      this.model,
      [this.modelRestrictions],
      [
        {key: 'email', value: value},
        {key: 'first_name', value: value},
        {key: 'last_name', value: value},
      ],
    ).then((response) => {
      console.log('response:', response);
      if (response.status == 'success') {
        this._clearDropdown();
        if (response.data.length <= 0) {
          this._displayNoResults();
          return;
        }
        console.log('response.data:', response.data);
        response.data.forEach((resDat) => {
          this._dropdown.appendChild(this._createLi(resDat));
        });
        this._showDropdown();
      }
    });
  }

  private _displayNoResults() {
    console.log('dipslayNo Resulst called.....');
    this._hideDropdown();
    const fn = this.options.onNoResults;
    return fn();
  }

  private _createNoResultsNode() {
    const mockData = {id: 0};
    var i = 0;
    this.requestedValues.forEach((key) => {
      if (i == 0) {
        mockData[key] = 'No Results Found';
      } else {
        mockData[key] = '';
      }
      i++;
    });

    const mockLI = this._createLi(mockData);
    mockLI.classList.remove('hover');
    mockLI.style.cursor = 'default';
    return mockLI;
  }

  private _clearDropdown() {
    this._dropdown.innerHTML = '';
  }

  private _createLi(data: any) {
    const li = document.createElement('li');
    li.style.padding = '4px 8px';
    li.style.margin = '0';
    li.style.cursor = 'pointer';
    li.classList.add('hover');
    li.innerHTML = this._setLiText(data);
    this._foundElementAttachCallback(li, this.cb);
    return li;
  }

  private _setLiText(data: any) {
    var str = `<input type="hidden" id="id" value="${data.id}" />`;
    this.requestedValues.forEach((key) => {
      str += `<input type="hidden" id="${key}" value="${data[key]}" />${data[key]} `;
    });
    return str;
  }

  private _hideDropdown() {
    console.log('hidedropdown called');
    clearInterval(this.mi);
    this._dropdown.style.display = 'none';
  }

  private _showDropdown() {
    console.log('SHOW DROPDOWN');
    this._setDropdownPosition();
    const fn = this.options.onResultsFound;
    if (typeof fn !== 'function') return;
    fn();
    this._dropdown.style.display = 'block';
    console.log('DROPDOWN ELEMENT: ', this._dropdown);
  }

  private _setDropdownPosition() {
    this._getElementBounds();
    if (this._thereIsYOverflow()) {
      console.log('overflow...');
      this._setDropdownAboveTargetElement();
    } else {
      console.log('no overflow found...');
      this._setDropdownBelowTargetElement();
    }
  }

  private _setDropdownXAxisValues() {
    this._dropdown.style.left = `${this._elementBounds.x}px`;
    this._dropdown.style.width = `${this._elementBounds.width}px`;
  }

  private _thereIsYOverflow() {
    const dropdownBounds = this._dropdown.getBoundingClientRect();
    return dropdownBounds.y + dropdownBounds.height > window.innerHeight;
  }

  private _setDropdownBelowTargetElement() {
    this._setDropdownXAxisValues();
    this._dropdown.style.top = `${
      this._elementBounds.y + this._elementBounds.height
    }px`;
  }

  private _setDropdownAboveTargetElement() {
    console.log('Setting dropdown above');
    this._setDropdownXAxisValues();
    const dropdownBounds = this._dropdown.getBoundingClientRect();

    console.log('this._dropdown:', this._dropdown);
    this._dropdown.style.top = `${
      this._elementBounds.y - dropdownBounds.height + 1
    }px`;
  }

  private _foundElementAttachCallback(el, callback) {
    el.addEventListener('click', (event) => {
      if (event.target !== el) return;
      const dataNodes = el.querySelectorAll('input');
      const c = {};
      dataNodes.forEach((node) => {
        c[node.id] = node.value;
      });
      if (typeof callback === 'function') {
        callback(c);
        this._hideDropdown();
        this._clearDropdown();
        (<HTMLInputElement>this._element).value = '';
        setTimeout(() => {
          this._setDropdownPosition();
        }, 500);
      }
    });
  }
}
