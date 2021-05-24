import {RuntimeConfigurationObject} from '../../Lib/Lib.js';
import {Request} from './Request.js';

export class Store extends Request {
  method: string = 'POST';
  public constructor(
    route: string,
    data: RuntimeConfigurationObject,
    options?: RequestInit,
    html: string = 'JSON',
    open: boolean = false,
  ) {
    super(route, data, options, html, open);

    this.options = this._setOptions(options);
  }
}
