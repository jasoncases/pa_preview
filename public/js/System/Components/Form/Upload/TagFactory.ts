interface UploadResponseObj {
  alt: string;
  newFileName: string;
  newpath: string;
  type: string;
  resolution: UploadResolution;
  size: number;
}

interface UploadResolution {
  x: number;
  y: number;
}

export class TagFactory {
  data: UploadResponseObj;
  img: Array<string> = ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'];
  vid: Array<string> = [
    'video/quicktime',
    'video/mpeg',
    'video/mpg',
    'video/mp4',
    'video/x-m4v',
  ];
  doc: Array<string> = ['application/pdf', 'application/postscript'];

  imgCounter: number = 0;
  vidCounter: number = 0;
  docCounter: number = 0;

  static __instance: TagFactory;

  public static getInstance() {
    if (!TagFactory.__instance) {
      TagFactory.__instance = new TagFactory();
    }
    return TagFactory.__instance;
  }

  public static create(uploadResponseObj) {
    return TagFactory.getInstance().build(uploadResponseObj);
  }

  private build(uploadResponseObj) {
    this.data = uploadResponseObj;
    const type = this.data.type;
    if (this.img.indexOf(type) >= 0) {
      return this.createImgTag();
    } else if (this.vid.indexOf(type) >= 0) {
      return this.createVidTag();
    } else if (this.doc.indexOf(type) >= 0) {
      return this.createDocTag();
    }
  }

  private createImgTag() {
    return {
      hash: this._generateHash('IMAGE', this.data.alt, ++this.imgCounter),
      html: `<img src="${this.data.newpath}"  alt="${this.data.alt}">`,
    };
  }
  private createVidTag() {
    return {
      hash: this._generateHash('VIDEO', this.data.alt, ++this.vidCounter),
      html: `<video src="${this.data.newpath}" controls autoplay></video>`,
    };
  }
  private createDocTag() {
    return {
      hash: this._generateHash('DOCUMENT', this.data.alt, ++this.docCounter),
      html: `<a href="${this.data.newpath}" target="_blank"><i class="fas fa-file-download"></i> -[ATTACHMENT] - ${this.data.alt}</a>`,
    };
  }

  private _generateHash(type: string, name: string, counter: number) {
    return `@[${type}-${counter}: ${name}]`;
  }
}
