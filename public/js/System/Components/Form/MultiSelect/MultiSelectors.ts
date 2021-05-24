import {Component} from '../../Component/Component.js';
import {MultiSelect} from './MultiSelect.js';

export interface MuliSelectedOptions {
  min: string;
  max: string;
  default: string;
}
export class MultiSelectors extends Component {
  _id: string = 'ui:form:multiselect';
  children: Array<MultiSelect> = [];
  public constructor() {
    super();
    this._init();
  }

  protected _registerAll() {
    this._registerNodeList();
  }

  protected _extendRegister() {
    this._nodeList.forEach(node => {
      this._createNewSelector(new MultiSelect(<HTMLElement>node));
    });
  }

  private _createNewSelector(MultiSelect: MultiSelect) {
    this.children.push(MultiSelect);
    MultiSelect.MultiSelectors = this;
  }

  public closeAll(node) {
    this.children.forEach(child => {
      if (child !== node) {
        child.close();
      }
    });
  }
}
