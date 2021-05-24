import {Fetch} from '../Fetch/Fetch.js';

interface LogMessage {
  message: string;
  level: string;
  data?: Array<any>;
}

export class Logger {
  url: string = '/log';
  public static info(msg: string, data?: Array<any>) {
    new Logger(msg, 'info', data);
  }
  public static error(msg: string, data?: Array<any>) {
    new Logger(msg, 'error', data);
  }
  public static warning(msg: string, data?: Array<any>) {
    new Logger(msg, 'warning', data);
  }
  public static critical(msg: string, data?: Array<any>) {
    new Logger(msg, 'critical', data);
  }

  public constructor(msg: string, level: string, data?: Array<any>) {
    this._post(msg, level, data);
  }

  private _post(msg: string, level: string, data?: Array<any>) {
    const obj = <LogMessage>{
      message: msg,
      level: level,
      data: data || [],
    };
    Fetch.store(this.url, obj).then((response) => console.log(response));
  }
}
