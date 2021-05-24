import {Attachment} from '../Interface.js';
import {EditNodeToWriteable} from './EditNodeToWriteable.js';
import {EditNodeToReadable} from './EditNodeToReadable.js';

interface SwapPairs {
  key: string;
  value: string;
}

export interface FormatterReturn {
  string: string;
  attachments: Array<Attachment>;
}

export class EditNodeFormatter {
  string: string;

  public constructor(string: string) {
    this.string = string;
  }

  public static toWriteable(string: string) {
    const enf = new EditNodeToWriteable(string);
    return enf.convert();
  }

  public static toReadonly(string: string, attachments: Array<Attachment>) {
    const enf = new EditNodeToReadable(string, attachments);
    return enf.convert();
  }
}
