import {Attachment} from '../Interface.js';
import {SwapPairs} from './EditNodeToWriteable.js';

export class EditNodeToReadable {
  string: string;
  attachments: Array<Attachment> = [];

  htmlEntities: Array<SwapPairs> = [
    {key: '&', value: '&amp;'},
    {key: '<', value: '&lt;'},
    {key: '>', value: '&gt;'},
  ];

  public constructor(string: string, attachments: Array<Attachment> = []) {
    this.string = this._asciiToHTML(string);
    this.attachments = attachments;
  }

  public convert() {
    this.attachments.forEach((node) => {
      console.log('toReadable convert node:', node);
      this.string = this.string.replace(node.hash, node.html);
    });
    return this.string;
  }

  private _asciiToHTML(string) {
    this.htmlEntities.forEach((node) => {
      string = string.replace(new RegExp(node.key, 'g'), node.value);
    });
    return string;
  }
}
