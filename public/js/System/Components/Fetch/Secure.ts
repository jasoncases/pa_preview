import {RuntimeConfigurationObject} from '../../Lib/Lib.js';
import {Request} from './Request.js';

/**
 *
 */
export class Secure extends Request {
  method: string = 'POST';
  public constructor(
    route: string,
    data: RuntimeConfigurationObject,
    options?: RequestInit,
    html: string = 'JSON',
    open: boolean = false,
  ) {
    super(route, data, options, html, open);

    options = {
      mode: 'no-cors',
      headers: {
        Authorization: 'Basic ' + window.btoa('secure'),
        'Content-type': 'application/x-www-form-urlencoded',
      },
    };
    this.options = this._setOptions(options);
  }

  /**
   * Alter setBody for Secure(), to obfuscate the password value
   */
  protected _setBody() {
    const c = [];
    Object.keys(this.data).forEach((key) => {
      const isPass = key === 'password' || key === 'pass';
      const value = isPass ? window.btoa(this.data[key]) : this.data[key];
      c.push(this._buildBodyString(key, value));
    });
    return c.join('&');
  }
}
