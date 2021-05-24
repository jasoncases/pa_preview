import {Attachment, AttachmentDetails} from '../Interface.js';

export class EditNodeVideoTag {
  re: RegExp = new RegExp(/(<video.*?><\/video>)/, 'gm');
  caps: Array<string> = [];
  string: string;
  type: string = 'VIDEO';
  counter: number = 0;
  public constructor(string) {
    this.string = string;
  }

  public static createNodes(string) {
    const envt = new EditNodeVideoTag(string);
    return envt.nodes();
  }

  public nodes() {
    const nodes = this.string.match(this.re);
    if (!nodes) return [];
    return nodes.map((node) => {
      this.counter++;
      return this._createAttachment(node);
    });
  }

  private _createAttachment(node) {
    console.log('node:', node);
    // const [z, width, src, alt] = [...Array.from(node.match(this.subRe))];
    return <Attachment>{
      hash: this._generateHash(),
      html: <string>node,
      details: <AttachmentDetails>{
        width: 0,
        src: '',
        alt: '',
      },
    };
  }

  private _generateHash(alt: string = 'video-file') {
    return `@[${this.type}-${this.counter}: ${alt}]`;
  }
}
