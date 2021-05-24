import {EditNodeATag} from './EditNodeATag.js';
import {FormatterReturn} from './EditNodeFormatter.js';
import {EditNodeImgTag} from './EditNodeImgTag.js';
import {EditNodeVideoTag} from './EditNodeVideoTag.js';

export interface SwapPairs {
  key: string;
  value: string;
}

export class EditNodeToWriteable {
  string: string;
  htmlEntities: Array<SwapPairs> = [
    {key: '&', value: '&amp;'},
    {key: '<', value: '&lt;'},
    {key: '>', value: '&gt;'},
  ];
  public constructor(string: string) {
    this.string = this._replaceAmpersands(string);
  }

  private _replaceAmpersands(string: string) {
    return string.replace(new RegExp('&amp;', 'g'), '&');
  }

  public convert() {
    const attachments = this._captureAllAttachements(this.string);
    attachments.forEach((node) => {
      this.string = this.string.replace(node.html, `${node.hash} \r\n`);
    });
    return <FormatterReturn>{
      string: this._htmlEntToASCII(this.string),
      attachments: attachments,
    };
  }

  private _htmlEntToASCII(string: string) {
    this.htmlEntities.forEach((node) => {
      string = string.replace(new RegExp(node.value, 'g'), node.key);
    });
    return string;
  }

  private _captureAllAttachements(string: string) {
    const c = [];
    c.push(...EditNodeImgTag.createNodes(string));
    c.push(...EditNodeVideoTag.createNodes(string));
    c.push(...EditNodeATag.createNodes(string));
    return c;
  }
}
