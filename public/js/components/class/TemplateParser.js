class TemplateParser {
   constructor() {
      //
   }
   /**
    *
    * @param {string} string
    * @param {Object} data
    * @return {string}
    */
   templateText(string, data) {
      var tempStrHolder = this.replace(string, data);
      return this.loop(tempStrHolder, data);
   }

   /**
    *
    * @param {Object}
    * @return {Object}
    */
   regexMatchAll(regExp, string) {
      const matches = [];
      while (true) {
         const match = regExp.exec(string);
         if (match === null) break;
         matches.push(match);
      }
      return matches;
   }

   /**
    *
    */
   replace(string, object) {
      const keys = Object.keys(object);
      keys.forEach(key => {
         // checks that key starts with a letter, to ignore array sets
         if (key.charAt(0).match(/[a-zA-Z]/)) {
            string = string.replace(new RegExp('{' + key + '}', 'gm'), object[key]);
         }
      });
      return string;
   }

   /**
    *
    */
   loop(string, object) {
      const pattern = /\[loop:begin\(([^]*?)\)\]([^]*?)\[loop:end\]/gm;
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

export default TemplateParser;
