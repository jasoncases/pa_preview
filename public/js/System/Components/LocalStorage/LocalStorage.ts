import {isJSON} from '../../Lib/Lib.js';

export class LocalStorage {
  public static store(key: string, value: any) {
    if (!isJSON(value)) {
      value = JSON.stringify(value);
    }
    localStorage.setItem(key, value);
  }
  public static get(key: string) {
    const data = localStorage.getItem(key);
    return JSON.parse(data);
  }
  public static destroy(key: string) {
    return localStorage.removeItem(key);
  }
}
