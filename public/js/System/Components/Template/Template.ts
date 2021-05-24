interface TemplateInterface {
  run(string: string, data: Object): string;
  regexMatchAll(pattern: RegExp, string: string): Array<any>;
  replace(string: string, data: Object): string;
  loop(string: string, data: Object): string;
}

export class Template implements TemplateInterface {
  constructor() {
    //
  }
  /**
   *
   * @param {string} string
   * @param {Object} data
   * @returns {void}
   */
  run(string: string, data: Object) {
    var tempStrHolder = this.replace(string, data);
    return this.loop(tempStrHolder, data);
  }

  /**
   *
   * @param {Object}
   *
   * @returns {Object}
   */
  regexMatchAll(regExp: RegExp, string: string) {
    const matches: Array<any> = [];
    while (true) {
      const match = regExp.exec(string);
      if (match === null) break;
      matches.push(match);
    }
    return matches;
  }

  /**
   *
   * @returns {string}
   */
  replace(string: string, data: Object) {
    const keys = Object.keys(data);
    keys.forEach(key => {
      // checks that key starts with a letter, to ignore array sets
      if (key.charAt(0).match(/[a-zA-Z]/)) {
        string = string.replace(new RegExp('{' + key + '}', 'gm'), data[key]);
      }
    });
    return string;
  }

  /**
   *
   * @returns {string}
   */
  loop(string: string, object: Object) {
    const pattern: RegExp = /\[loop:begin\(([^]*?)\)\]([^]*?)\[loop:end\]/gm;
    let matches = [...this.regexMatchAll(pattern, string)];
    if (typeof matches !== 'undefined' && matches.length > 0) {
      matches = matches[0];
      const stringToReplace = matches[0];
      const array = matches[1];
      const stringToLoop = matches[2];
      let outputString = '';
      if (typeof object[array] !== 'undefined') {
        object[array].forEach(subArr => {
          outputString += this.replace(stringToLoop, subArr);
        });
      } else {
        outputString = '';
      }
      return string.replace(stringToReplace, outputString);
    } else {
      return string;
    }
  }
}
