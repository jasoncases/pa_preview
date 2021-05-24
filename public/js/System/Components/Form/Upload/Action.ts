import {handleFetchResponse} from '../../../Lib/Lib.js';
import {Fetch} from '../../Fetch/Fetch.js';

export class Action {
  public static async upload(formData) {
    const request = await fetch('/upload', {
      method: 'POST',
      mode: 'no-cors',
      headers: {},
      body: formData,
    });
    return await request.text().then((response) => {
      return handleFetchResponse(request, response);
    });
  }
  public static async getFileTypes() {
    return Fetch.get('/system/batch', {key: 'allowed_upload_file_type'});
  }
  public static async getAllowedExtensions() {
    return Fetch.get('/system/get', {key: 'allowed_extensions'});
  }
}
