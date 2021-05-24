import {Attachment, AttachmentDetails} from '../Interface.js';

export class EditNodeImgTag {
  re: RegExp = new RegExp(/(<img.*?>)/, 'gm');
  subRe: RegExp = new RegExp(/src=\"(.*?)\" alt=\"(.*?)\"/);
  caps: Array<string> = [];
  string: string;
  type: string = 'IMAGE';
  counter: number = 0;
  public constructor(string) {
    this.string = string;
  }

  public static createNodes(string) {
    const enit = new EditNodeImgTag(string);
    return enit.nodes();
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
    try {
      console.log('node:', node);
      const [z, src, alt] = [...Array.from(node.match(this.subRe))];
      return <Attachment>{
        hash: this._generateHash(<string>alt),
        html: <string>node,
        details: <AttachmentDetails>{
          src: src,
          alt: alt,
        },
      };
    } catch (e) {
      console.error(
        e,
        'Error parsing image in timeline edit module. Likely the "width" tag is out of sync',
      );
    }
  }

  private _generateHash(alt: string) {
    return `@[${this.type}-${this.counter}: ${alt}]`;
  }
}
