import { Throw } from '../../../Throwable/Throw.js'
import { ComponentInterface } from '../../Interface/Component/Component.js'
import { FetchResponseInterface, isAncestor } from '../../Lib/Lib.js'

export class Component implements ComponentInterface {
  Ui: any
  // protected props
  _id: string
  _targetId: string
  _parentId: string
  // element pointers
  _element: HTMLElement
  _parent: HTMLElement
  _target: HTMLElement
  _nodeList: NodeList

  /**
   *
   */
  public init() {
    this._init()
  }

  /**
   * Returns the main element property of the Component
   */
  public getElement() {
    return this._element
  }

  public getParent() {
    return this._parent
  }

  public getTarget() {
    return this._target
  }

  public getId() {
    return this._id
  }

  public setId(id: string) {
    this._id = id
  }

  /**
   *
   * @param el
   */
  public registerTargetElementByParent(el: HTMLElement) {
    this._target = el
  }

  public registerTargetById(id: string = null) {
    this._targetId = id ? id : this._targetId
    this._registerTargetFromElementChildrenByTargetId()
  }

  /**
   *
   */
  protected _init() {
    try {
      this._registerAll()
      this._initListeners()
      this._extendInit()
      this._extendRegister()
      this._extendListeners()
    } catch (e) {
      // console.error('Error in Component.ts', e)
    }
  }

  protected _registerNodeList() {
    this._nodeList = document.querySelectorAll(`[id^="${this._id}"]`)
  }

  protected _registerAll() { }

  protected _extendInit() { }

  protected _extendRegister() { }

  protected _extendListeners() { }

  /**
   *
   */
  protected _registerElement() { }

  /**
   *
   */
  protected _registerElementById() {
    this._element = document.getElementById(this._id)
    if (!this._element) Throw.err('ElementNotFound', this._id)
    this._extendElement()
  }

  protected _registerElementByDataId() {
    this._element = document.querySelector(`[data-id="${this._id}"]`)
    if (!this._element) Throw.err('ElementNotFound', this._id)
    this._extendElement()
  }

  protected _extendElement() { }
  /**
   *
   */
  protected _initListeners() { }

  protected _initDefaultListener() {
    this._element.addEventListener('click', (event) =>
      this._mouseClickContainer(event)
    )
  }
  /**
   *
   * @param event
   */
  protected _elementListener(action: string, methodCallback: any) {
    try {
      if (typeof this[methodCallback] !== 'function') {
        throw `methodCallback must be a function. Type: {${typeof this[
        methodCallback
        ]}} found.`
      }
      this._element.addEventListener(action, (event) =>
        this[methodCallback](event)
      )
    } catch (e) {
      console.error(e)
    }
  }
  /**
   *
   * @param event
   */
  protected _mouseClickContainer(event: MouseEvent) {
    this._action(event)
  }

  /**
   *
   * @param event
   */
  protected _action(event: MouseEvent) { }

  protected _registerParentById() {
    this._parent = this._get(this._parentId)
  }

  protected _registerTargetElementByTargetId() {
    this._target = document.getElementById(this._targetId)
  }
  protected _registerTargetFromElementChildrenByTargetId() {
    this._target = <HTMLElement>Array.from(this._element.children).filter(
      (el) => {
        return el.id === this._targetId
      }
    )[0]
  }
  /**
   *
   */

  protected _get(id: string) {
    return document.getElementById(id)
  }

  protected _appendElementToParent() {
    this._parent.appendChild(this._element)
  }

  protected _appendElementToTarget() {
    this._target.appendChild(this._element)
  }

  protected _getData(name: string) {
    const node = <HTMLInputElement>document.getElementById(`data:${name}`)
    if (!node) return
    return node.value
  }

  protected _setData(name: string, value: string) {
    const node = <HTMLInputElement>document.getElementById(`data:${name}`)
    if (!node) return
    node.value = value
  }

  protected _removeElement() {
    this._element.remove()
  }

  protected _isMobile() {
    return window.innerWidth < 800
  }
  protected _isMedium() {
    return window.innerWidth <= 1366
  }

  protected _handleResponse(res: FetchResponseInterface) {
    if (res.status === 'success') return this._runOnSuccess(res)
    return this._runOnFailure(res)
  }

  protected _runOnSuccess(res: FetchResponseInterface) {

  }

  protected _runOnFailure(res: FetchResponseInterface) {

  }
}
