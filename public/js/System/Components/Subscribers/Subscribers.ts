import {
  applyCSSClasses,
  FetchResponseInterface,
  gnuGet,
  hash64,
  identityHash,
  isMobile,
  outsideClick,
} from '../../Lib/Lib.js'
import { DropdownComponent } from '../Component/DropdownComponent.js'
import { Fetch } from '../Fetch/Fetch.js'
import { Response } from '../Response/Response.js'

export interface SubscriberInterface {
  id: number
  email: string
  displayName: string
  fullDisplayName: string
}

export class Subscribers extends DropdownComponent {
  elementClassName: string = 'subscriber-component'

  // collaborators
  dropdown: Dropdown
  options: SubscriberOptions
  query: Query

  // container for selected options. On close, these options get sent to
  // the server via the input option.url prop
  selected: Array<Option> = []
  rendered: Array<Option> = []
  // selected: Set<Option> = new Set()
  // rendered: Set<Option> = new Set()
  // * All props below this point can be changed by the options object
  // * provided to the constructor

  // default settings - can be changed via options of the same prop name
  title: string = 'Edit record subscribers'
  placeHolder: string = 'Enter email'

  // the element to which this component gets appended
  target: HTMLElement
  mobileTarget: HTMLElement

  // aux elements
  mobileCloser: HTMLButtonElement

  /**
   * Incoming data will have a unique identifier, by default it should
   * usually be the id value in the OptionRecord, but if that needs to
   * change, it can be done so via the options
   */
  identifier: string = 'id'

  // short pause boolean to prevent some conflicts w/ the outsideClick
  pause: boolean = false
  pauseDelay: number = 200

  //
  dropdownOptions: any = {
    showClearAll: false,
  }

  /**
   * Default ignore array. These values will get ignored when using the
   * hashIdentity() function
   */
  hashIgnore: Array<string> = ['checked', 'index']

  // settings properties
  showAvatars: boolean = false

  // query properties
  url: string = null
  // string that maps to a specific model, `taskSubscriber`
  queryType: string = null
  // the fields from the db to return
  fields: Array<string> = []
  // the field that is actually searched for the provided value
  targetField: string = null
  // an array of QueryModfiers:  [{key:, operator:, value:}]
  modifier: Array<QueryModifier> = []

  /**
   * These are defined by the children to be called on open and closed
   */
  public closeCallback() {}
  public openCallback() {}

  /**
   * Options object can have the following properties
   *
   * title        - Text that appears in the top row of the component
   * placeHolder  - placeholder text on input field
   * queryType    - keyword so the server knows which model to qry
   * targetField  - the db field that is searched
   * fields       - Array of fields to return from the query
   * identifier   - override 'id' as default identifier
   * modifier     - key-op-value obj to modify query
   * ignore       - props to ignore in the hashIdentity function
   * target       - string id of the target el this element appends to
   * showAvatars  - false hides user avatar block [default]
   * closeCallback- callback function to run on component close
   * openCallback - callback function to run on component open
   *
   * @param options
   */
  public constructor(options: SubscriberOptions) {
    super()
    this._registerOptions(options)
    outsideClick(this)
    this._init()
  }

  public isOpen() {
    return this._element.classList.contains('open')
  }

  public getIgnore() {
    return this.hashIgnore
  }

  public close(evt?: any) {
    if (!this.isOpen()) return
    if (evt) {
      if (evt.target === this._element) return
      return this._setOpenState(false)
    }
    this._setOpenState(false)
  }

  public reset() {
    this.selected.length = 0
    this.dropdown.reset()
  }

  public getAncestor() {
    return this._element
  }

  public getSelected() {
    return this.selected
  }

  public getRenderedHashes() {
    const hashLabels: any = document.querySelectorAll(
      '.option-dropdown-item>input'
    )
    const hashSet = new Set()

    hashLabels.forEach((hash) => {
      if (!hash.checked) return
      console.log('Hashes:', hashSet.add(hash))
    })
  }
  public clearText() {
    this.query.clearText()
  }

  public addOption(option: OptionRecord) {
    this.dropdown.addOption(option)
  }

  public clearSuggestions() {
    this.dropdown.clearSuggestions()
  }

  public addSuggestions(suggestions: Array<OptionRecord>) {
    this.dropdown.addSuggestions(suggestions)
  }

  public addMetaOption(option: Option) {
    if (this._getSelectedIdentityHashes().indexOf(
      identityHash(option.option, this.hashIgnore)
    ) >= 0) {
      console.warn('Attempt to override...', option)
      return Response.put('caution', 'A matching option already exists')
    }
    this.rendered.push(option)
    this.selected.push(option)
  }

  public removeMetaOption(option: Option) {
    this.selected = this.selected.filter((opt) => {
      return opt !== option
    })
    // this.selected.forEach((opt) => {
    //   return opt !== option
    // })
  }

  public clearOptions() {
    this.selected.length = 0
    this.rendered.length = 0
    // this.selected.clear()
    // this.rendered.clear()
  }

  public generate() {
    this._element = this._createElement()
    this.dropdown = this._registerDropdown(new Dropdown(this.dropdownOptions))
    this._registerQuery(new Query(this._buildQuerySettings()))
    this._build()
    this._registerMobileCloser()
    this._onBuild()
  }

  public runOnNoRecordsFound() {}

  private _close() {
    this._element.classList.remove('open')
  }

  protected _registerMobileCloser() {
    this.mobileCloser = <HTMLButtonElement>gnuGet('!mobile-closer')
    console.log('this.mobileCloser - :', this.mobileCloser)
    if (this.mobileCloser)
      this.mobileCloser.addEventListener('click', (e) => {
        this._setOpenState(false)
      })
  }

  private _buildQuerySettings() {
    return <QuerySettings>{
      url: this.url,
      fields: this.fields,
      queryType: this.queryType,
      targetField: this.targetField,
      modifier: this.modifier,
    }
  }

  private _getSelectedIdentityHashes() {
    return [...this.rendered].map((opt) => {
      return identityHash(opt.option, this.hashIgnore)
    })
  }

  private _registerDropdown(dropdown: Dropdown) {
    dropdown.parent = this
    dropdown.showAvatars = this.showAvatars
    dropdown.identifier = this.identifier
    return dropdown
  }

  private _registerQuery(query: Query) {
    this.query = query
    query.subscribers = this
    query.setInput(this._element.querySelector('input'))
    return query
  }

  protected _runClosedCallback() {
    console.log('testing run closed callback')
    if (typeof this.closeCallback === 'function') this.closeCallback()
    this._allowForTogglePause()
    // this._close()
  }

  private _allowForTogglePause() {
    this.pause = true
    setTimeout(() => {
      this.pause = false
    }, this.pauseDelay)
  }

  protected _runOpenCallback() {
    console.log('testing run closed callback')
    if (typeof this.openCallback === 'function') this.openCallback()
    this.dropdown.redrawSelected(this.selected)
    this.rendered = [...this.selected]
    this.dropdown.open()
  }

  protected _onBuild() {}

  private _build() {
    this._element.appendChild(this.dropdown.getElement())
    const target = this._determineTarget()
    target.appendChild(this._element)
  }

  private _determineTarget() {
    if (isMobile() && Boolean(this.mobileTarget)) {
      return this.mobileTarget
    } else {
      return this.target
    }
  }

  private _createElement() {
    const ul = document.createElement('ul')
    ul.className = this.elementClassName
    ul.innerHTML = this._template()
    return ul
  }

  protected _setOpenDisplay() {
    this._element.classList.add('open')
  }

  protected _setClosedDisplay() {
    this._element.classList.remove('open')
  }

  private _template() {
    return `<li class="flex-row flex-between title-row">
              <span class="title">${this.title}</span>
              <span class="mobile">
                <button type="button" class="mobile-closer" data-id="mobile-closer">
                  <i class="far fa-times"></i>
                </button>
              </span>
            </li>
            <li class="flex-row flex-center input-row">
              <input type="text" placeholder="${this.placeHolder}" />
            </li>`
  }

  private _registerOptions(options: SubscriberOptions) {
    console.log('options:', options)
    if (!options) return
    if (typeof options['title'] === 'string') this.title = options.title
    if (typeof options['placeHolder'] === 'string')
      this.placeHolder = options.placeHolder
    if (typeof options['url'] === 'string') this.url = options['url']
    if (typeof options['queryType'] === 'string')
      this.queryType = options['queryType']
    if (typeof options['fields'] === 'object') this.fields = options['fields']
    if (
      typeof options['targetField'] === 'string' ||
      typeof options['targetField'] === 'object'
    )
      this.targetField = options['targetField']
    if (typeof options['modifier'] === 'object')
      this.modifier = options['modifier']
    if (typeof options['showAvatars'] === 'boolean')
      this.showAvatars = options['showAvatars']
    if (typeof options['closeCallback'] === 'function')
      this.closeCallback = options['closeCallback']
    if (typeof options['openCallback'] === 'function')
      this.openCallback = options['openCallback']
    if (typeof options['identifier'] === 'string')
      this.identifier = options['identifier']
    if (typeof options['ignore'] === 'object')
      this.hashIgnore = options['ignore']
    if (typeof options['target'] === 'string')
      this.target = gnuGet(options['target'])
    if (typeof options['mobileTarget'] === 'string')
      this.mobileTarget = gnuGet(options['mobileTarget'])
    if (typeof options['showClearAll'] === 'boolean') {
      console.log(
        'testing test--------------------------',
        options['showClearAll']
      )
      this.dropdownOptions.showClearAll = options['showClearAll']
    }
  }
}

/**
 * Contains the Selected options and the Suggestions options that are
 * pulled in and rendered via the search. This class defines the drpdown
 * template and assigns each child to their target element
 */
class Dropdown {
  parent: Subscribers
  suggestions: Suggestions
  selected: Selected

  showAvatars: boolean
  identifier: string

  _element: HTMLElement

  options: any = {
    showClearAll: false,
  }

  public constructor(options: any) {
    this.options = options
    this._init()
  }

  public setAttribute(key: string, value: any) {
    this[key] = value
  }

  public getIgnore() {
    return this.parent.getIgnore()
  }

  public reset() {
    this.selected.reset()
    this.suggestions.reset()
  }

  public closeSuggestions() {
    this.suggestions.close()
  }

  public openSuggestions() {
    this.suggestions.open()
  }

  // public redrawSelected(selected: Array<Option>) {
  //   this.selected.redraw(selected)
  // }
  public redrawSelected(selected: Array<Option>) {
    this.selected.redraw(selected)
  }

  public getSelected() {
    return this.parent.getSelected()
  }

  public clearSuggestions() {
    this.suggestions.clear()
  }

  public suggestionIsOpen() {
    return this.suggestions.isOpen()
  }

  public getElement() {
    return this._element
  }

  public open() {
    this.selected.open()
  }

  public openSelected() {
    this.selected.open()
  }

  public closeSelected() {
    this.selected.close()
  }

  public addMetaOption(option: Option) {
    this.parent.addMetaOption(option)
  }

  public addToSelected(option: Option) {
    this.addOption(option.option)
  }

  public removeMetaOption(option: Option) {
    this.parent.removeMetaOption(option)
  }

  public clearText() {
    this.parent.clearText()
  }

  public runOnNoRecordsFound() {
    this.parent.runOnNoRecordsFound()
  }

  /**
   *
   * @param option
   */
  public addOption(option: OptionRecord) {
    // add these options to the incoming OptionRecord before pushing to
    // selected
    option.checked = 'checked'
    option.showAvatars = this.showAvatars
    option.identifier = this.identifier
    this.selected.addOption(option)
  }

  /**
   * Recieves the server response array from the server search and
   * passes it to Suggestions
   * @param suggestions
   */
  public addSuggestions(suggestions: Array<OptionRecord>) {
    this.suggestions.renderRecords(suggestions)
  }

  private _init() {
    this._element = this._createElement()
    this._registerSuggestions()
    this._registerSelected()
    this._registerClearAll()
  }

  private _registerSelected() {
    this.selected = new Selected(<HTMLElement>this._element.children[0])
    this.selected.parent = this
  }

  private _registerSuggestions() {
    this.suggestions = new Suggestions(<HTMLElement>this._element.children[1])
    this.suggestions.parent = this
  }

  /**
   * This button may or may not be moved to the Selected class, however
   * the functionality seems a little more meta than the Selected should
   * be handling
   */
  private _registerClearAll() {
    const clearAll = this._element.querySelector('[data-id="clear-all"]')
    if (!clearAll) return
    clearAll.addEventListener('click', (e) => this._removeAllOptions(e))
  }

  private _removeAllOptions(e: Event) {
    this.selected.reset()
    this.selected.close()
    this.parent.clearOptions()
    this.parent.close()
  }

  private _createElement() {
    const li = document.createElement('li')
    li.className = 'options-dropdown'
    li.innerHTML = this._template()
    return li
  }

  private _template() {
    return `<ul class="selected" data-id="selected">
            ${this._insertClearAll()}
            </ul>
            <ul class="suggestions hide" data-id="suggestions">
              <li class="scod-header add-pad">
                <span data-id="header-notify">Records found:</span>
                <button type="button" class="suggestion-clear" data-id="clear-suggestions">back</button>
              </li>
            </ul>`
  }

  private _insertClearAll() {
    if (!this.options.showClearAll) return ''
    return `<li class="scod-header">
              <button type="button" class="clear" data-id="clear-all">
                <span>
                  <i class="far fa-times"></i>
                </span>
                <span>Clear all</span>
              </button>
            </li>`
  }
}

/**
 * Rendering and container of selected options. When added, the option
 * gets created, rendered and the Option class is pushed back up to the
 * Subscribers class to be stored in the selected[] array.
 *
 */
class Selected {
  _element: HTMLElement
  parent: Dropdown
  _options: Array<Option> = []

  public constructor(el: HTMLElement) {
    this._element = el
  }

  public reset() {
    this._clearOptions()
  }

  private _clearOptions() {
    this._options.length = 0
    while (this._element.children.length > 1) {
      this._element.removeChild(this._element.lastChild)
    }
  }

  // public redraw(selected: Array<Option>) {
  //   this._clearOptions()
  //   this._options = [...selected]
  //   this._options.forEach((opt) => this._element.appendChild(opt.getElement()))
  // }

  public redraw(selected: Array<Option>) {
    this._clearOptions()
    this._options = [...selected]
    this._options.forEach((opt) => this._element.appendChild(opt.getElement()))
  }
  /**
   * Passes an option object up to the Subscribers class to be added to
   * selected[]
   *
   * @param option
   */
  public addMetaOption(option: Option) {
    this.parent.addMetaOption(option)
  }

  /**
   * Passes an option object up to the Subcribers class to be removed
   * from selected []
   *
   * @param option
   */
  public removeMetaOption(option: Option) {
    this.parent.removeMetaOption(option)
  }

  public close() {
    this._element.classList.add('hide')
  }

  public open() {
    if (this._options.length <= 0) return this.close()
    if (this.parent.suggestionIsOpen()) return this.close()
    this._element.classList.remove('hide')
  }

  public addOption(option: OptionRecord) {
    const opt = this._createOption(option)
    this._element.appendChild(opt.getElement())
    this._options.push(opt)
    this.addMetaOption(opt)
    this.open()
  }

  private _createOption(option: OptionRecord) {
    const opt = new Option(option)
    opt.parent = this
    return opt
  }
}

class Suggestions {
  _element: HTMLElement
  parent: Dropdown
  options: Array<Option> = []

  public constructor(el: HTMLElement) {
    this._element = el
    this._registerClearButton()
  }

  public reset() {
    this.clear()
    this.parent.clearText()
  }

  public addMetaOption(option: Option) {
    console.warn('Subscriber this option:', option)
    this.parent.addToSelected(option)
  }

  public removeMetaOption(option: Option) {
    this.parent.removeMetaOption(option)
  }

  public isOpen() {
    return !this._element.classList.contains('hide')
  }

  public close() {
    this._element.classList.add('hide')
  }

  public open() {
    this.parent.closeSelected()
    this._element.classList.remove('hide')
  }

  public clear() {
    this._clearRecords()
    this.close()
    this.parent.openSelected()
  }

  public renderRecords(records: Array<OptionRecord>) {
    this._clearRecords()
    if (!records) return this._displayNoResults()
    const cleanedRecords = this._cleanseIncomingRecords(records)
    if (cleanedRecords.length <= 0) return this._displayNoResults()
    this._displayResultsFound()
    cleanedRecords.forEach((rec) => {
      const opt = this._createOption(rec)
      this._element.appendChild(opt.getElement())
      this.options.push(opt)
    })
    this.open()
  }

  private _clearRecords() {
    while (this._element.children.length > 1) {
      this._element.removeChild(this._element.lastChild)
    }
  }

  /**
   * Change the header
   */
  private _displayResultsFound() {
    const notify = <HTMLElement>(
      this._element.querySelector('[data-id="header-notify"]')
    )
    notify.innerText = 'Records found:'
  }

  private _displayNoResults() {
    const notify = <HTMLElement>(
      this._element.querySelector('[data-id="header-notify"]')
    )
    notify.innerText = 'No records found'
    this.open()
    this.parent.runOnNoRecordsFound()
  }

  private _createOption(option: OptionRecord) {
    const opt = new Option(option)
    opt.parent = this
    return opt
  }

  private _registerClearButton() {
    const clr = this._element.querySelector('[data-id="clear-suggestions"]')
    clr.addEventListener('click', (e) => this.reset())
  }

  /**
   * Compares incoming records' identityHash() value. See
   * System/Lib/Lib.ts for more info on idendityHash function
   *
   * @param records
   */
  private _cleanseIncomingRecords(records: Array<OptionRecord>) {
    const ignore = this.parent.getIgnore()
    const selectedComp = this.parent.getSelected().map((opt) => {
      return identityHash(opt.option, ignore)
    })
    // const selectedComp = this.parent
    //   .getSelected()
    //   .forEach((opt) => identityHash(opt.option, ignore))
    return records.filter((rec) => {
      return selectedComp.indexOf(identityHash(rec, ignore)) < 0
    })
  }
}

class Option {
  parent: any
  option: OptionRecord
  showAvatars: boolean
  _element: HTMLElement
  _input: HTMLInputElement

  public constructor(option: OptionRecord) {
    this.option = option
    this._init()
  }

  private _init() {
    this._element = this._createElement()
    this._registerInput()
  }

  public getElement() {
    return this._element
  }

  public isSelected() {
    return Boolean(this._input.checked)
  }

  private _registerInput() {
    this._input = this._element.querySelector('input')
    this._input.addEventListener('change', (e) => this._handleChange(e))
  }

  private _handleChange(e: Event) {
    if (this._input.checked) {
      this.parent.addMetaOption(this)
    } else {
      this.parent.removeMetaOption(this)
    }
  }

  private _createElement() {
    const li = document.createElement('li')
    li.innerHTML = this._template()
    return li
  }

  private _template() {
    const idHash = hash64(JSON.stringify(this.option), true)
    return `<label for="${idHash}" class="option-dropdown-item">
              <input type="checkbox" id="${idHash}" value="${
      this.option[this.option.identifier]
    }" ${this.option.checked} />
              <span class="option-item-checkbox"></span>
              ${
                this.showAvatars
                  ? `<span class="option-item-avatar" ></span>`
                  : ``
              }
              <span class="option-item-text overflow-ellipses">${
                this.option[this.option.renderKey]
              }</span>
            </label>`
  }
}

class Query {
  subscribers: Subscribers
  input: HTMLInputElement
  url: string = '/subscriber_component'
  settings: QuerySettings

  public constructor(settings: QuerySettings) {
    this.settings = settings
    this.settings.url = settings.url ?? this.url
  }

  public clearText() {
    this.input.value = ''
  }

  public setInput(input: HTMLInputElement) {
    this.input = input
    this._initListener()
  }

  private _initListener() {
    this.input.addEventListener('keyup', (e) => this._keyup(e))
  }

  private _keyup(e: KeyboardEvent) {
    if (this.input.value.length < 3) return this._clearSuggestions()
    this._query(this.input.value).then((res) => this._handleResponse(res))
  }

  private async _query(val: string) {
    return Fetch.get(this.settings.url, {
      query: val,
      queryType: this.settings.queryType,
      fields: this.settings.fields,
      targetField: this.settings.targetField,
      modifier: this.settings.modifier ?? [],
    })
  }

  private _clearSuggestions() {
    this.subscribers.clearSuggestions()
  }

  protected _handleResponse(res: FetchResponseInterface) {
    if (res.status === 'success') {
      return this._runOnSuccess(res)
    } else {
      return this._runOnFailure(res)
    }
  }

  protected _runOnSuccess(res: FetchResponseInterface) {
    this.subscribers.addSuggestions(res.data.suggestions)
  }

  protected _runOnFailure(res: FetchResponseInterface) {}
}

export interface SubscriberOptions {
  title: string
  placeHolder: string
}

export interface OptionRecord {
  id: number
  renderKey: string
  index?: number
  checked?: string
  [key: string]: any
}

interface QuerySettings {
  url: string
  queryType: string
  fields: Array<string>
  targetField: string
  modifier?: Array<QueryModifier>
}

interface QueryModifier {
  key: string
  operator?: string
  value: any
}
