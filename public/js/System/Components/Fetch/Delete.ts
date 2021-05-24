import {Request} from './Request.js';
import {RuntimeConfigurationObject} from '../../Lib/Lib.js';

export class Delete extends Request {
  method: string = 'DELETE';
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

  protected _finalize() {
    // if (this.data) {
    //   this.route += this._createQueryStringData();
    // }
  }

  protected _createQueryStringData() {
    const append = [];
    Object.keys(this.data).forEach((key) => {
      append.push(`${key}=${JSON.stringify(this.data[key])}`);
    });
    return `?${append.join('&')}`;
  }
}
