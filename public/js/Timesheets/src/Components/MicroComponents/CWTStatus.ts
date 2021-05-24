import { Status } from './Status.js';

export class CWTStatus extends Status {
  public constructor() {
    super();
    this._id = 'CWT-status-container'; // override the id value
  }
}
