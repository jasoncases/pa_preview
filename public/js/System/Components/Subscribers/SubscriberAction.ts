import { Fetch } from "../Fetch/Fetch.js"
import { SubscriberInterface } from "./Subscribers.js"




export class SubscriberAction {

  action: string
  module: string
  subscriberList: Array<SubscriberInterface>

  public constructor(action: string) {
    this.action = action
  }

  public static AddGuest() {
    return new SubscriberAction('addGuest')
  }

  public static Update() {
    return new SubscriberAction('update')
  }

  public Task() {
    return this._setModule('task')
  }

  public Code() {
    return this._setModule('code')
  }

  public Ticket() {
    return this._setModule('ticket')
  }

  public Execute(options: any) {
    console.log('SubscriberAction aggregator called: ', this, options)
    return this[this.action](this.module, options)
  }

  private async addGuest(module: string, options: any) {
    return Fetch.store('/subscriber_component', {
      module: module,
      email: options.email,
    })
  }

  private async update(module: string, task_id: number, subscriberList: Array<SubscriberInterface>) {
    return Fetch.update('/subscriber_component', {
      module: module,
      task_id: task_id,
      subscriberList: subscriberList,
    })
  }

  private _setModule(module: string) {
    this.module = module
    return this
  }
}