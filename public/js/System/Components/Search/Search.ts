import {Fetch} from '../Fetch/Fetch.js';

interface KeyVal {
  key: string;
  value: any;
}

export class Search {
  route: string = '/search/t';
  orSearch: Array<KeyVal>;
  andSearch: Array<KeyVal>;
  model: string;

  public constructor(
    model: string,
    andSearch: Array<KeyVal>,
    orSearch: Array<KeyVal>,
  ) {
    this.model = model;
    this.andSearch = andSearch;
    this.orSearch = orSearch;
  }

  /**
   *
   * @param string model Corresponds to a ORM model in the PHP framework
   * @param Array<KeyVal> search
   */
  public static find(
    model: string,
    andSearch: Array<KeyVal>,
    orSearch: Array<KeyVal> = null,
  ) {
    const ns = new Search(model, andSearch, orSearch);
    return ns.result();
  }

  private async result() {
    return Fetch.get(this.route, {
      model: this.model,
      andSearch: this.andSearch,
      orSearch: this.orSearch,
    });
  }
}
