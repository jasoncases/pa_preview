import {Request} from './Request.js';
import {RuntimeConfigurationObject} from '../../Lib/Lib.js';

export class Put extends Request {
  method: string = 'PUT';
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
