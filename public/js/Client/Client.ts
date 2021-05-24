interface ClientInterface {
  //
}
interface ClientRuntimeObject {
  [k: string]: string;
}

export class Client implements ClientInterface {
  //
  _globals: ClientRuntimeObject = {};
  _listeners: any;
  __CACHE: any;

  private static instance: Client;

  private constructor() {
    this._listeners = [];
    this._load();
  }

  public static getInstance(): Client {
    if (!Client.instance) {
      Client.instance = new Client();
    }
    return Client.instance;
  }

  private _load() {
    this._loadClient();
  }

  private async _loadClient() {
    const request = await fetch('/client');
    await request.json().then((data) => {
      console.log('loaded Client data:', data);
      this._runLoadedData(data);
      console.log('TypeScript Rewrite: Client: ', this);
    });
  }

  private _runLoadedData(data) {
    this._parseLoadedData(data);
  }

  private _parseLoadedData(data: any) {
    if (!this._compare(data)) {
      Object.keys(data).forEach((key) => {
        this[`_${key}`] = data[key];
      });
      this._updateListeners();
      this._cache(data);
    }

    console.log('Client: ', this);
  }

  private _compare(data) {
    return this.__CACHE === JSON.stringify(data);
  }
  private _cache(data) {
    this.__CACHE = JSON.stringify(data);
  }

  public getGlobal(attr: string) {
    return this._globals[attr];
  }

  registerListener(listener: any) {
    this._listeners.push(listener);
  }

  _updateListeners() {
    this._listeners.forEach((item) => {
      item.update();
    });
  }

  public reload() {
    this._load();
  }
}
