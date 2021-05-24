import {Attachment, AttachmentDetails} from '../Interface.js';

export class EditNodeATag {
  re: RegExp = new RegExp(/(<a.*?<\/a>)/, 'gm');
  caps: Array<string> = [];
  string: string;
  counter: number = 0;
  type: string = 'LINK';

  public constructor(string) {
    this.string = string;
  }

  public static createNodes(string) {
    const enat = new EditNodeATag(string);
    return enat.nodes();
  }

  public nodes() {
    const nodes = this.string.match(this.re);
    if (!nodes) return [];
    return nodes.map((node) => {
      this.counter++;
      return this._createAttachment(node);
    });
  }

  private _createAttachment(node: string) {
    try {
      return <Attachment>{
        hash: this._generateHash(node),
        html: node,
        details: <AttachmentDetails>{
          width: 0,
          src: '',
          alt: 'alt',
        },
      };
    } catch (e) {
      console.error('Error parsing EditNodeATag found nodes');
    }
  }

  private _generateHash(name: string) {
    return `@[${this.type}-${this.counter}]`;
  }
}
