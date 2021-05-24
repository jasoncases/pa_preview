// String.prototype.matchAll
if (typeof String.prototype.matchAll === 'undefined') {
  String.prototype.matchAll = function(regExp) {
    const matches = [];
    while (true) {
      const match = regExp.exec(this.toString());
      if (match === null) break;
      matches.push(match);
    }
    return matches;
  };
}

if (typeof Array.prototype.pushIfNot === 'undefined') {
  Array.prototype.pushIfNot = function(el) {
    if (this.indexOf(el) < 0) {
      this.push(el);
    }
  };
}
// check if defined, currently NOT in the object prototype
// if (typeof Object.prototype.objForEach === 'undefined') {
//   //   define it and take the callback
//   Object.prototype.objForEach = function(callBack) {
//     if (typeof callBack !== 'function') return; // bounce if it's not a function
//     // get the keys
//     const keys = Object.keys(this);
//     // iterate through the keys and apply the callback to each prop
//     keys.forEach(key => {
//       callBack(this[key]);
//     });
//   };
// }

const isObjectEmpty = function(obj) {
  for (var key in obj) {
    if (obj.hasOwnProperty(key)) return false;
  }
  return true;
};

const DateFormat = function() {
  /**
   * Currently accepted formats, model taken from PHP date(): https://www.php.net/manual/en/function.date.php
   * My other main focus is PHP development, so this made sense to keep it similar.
   */
  this.formats = {
    Y: 'getFullYear', // YYYY
    y: 'getYear', // YY
    M: 'getMonth', // 01-12
    F: 'getMonth', // 1-12
    m: 'getMonth', // January-December
    f: 'getMonth', // Jan-Dec
    D: 'getDate', // 01-31
    d: 'getDate', // 1-31
    H: 'getHours', // 00-23
    h: 'getHours', // 12-12
    i: 'getMinutes', // 00-59
    s: 'getSeconds', // 0-59
    S: 'getSeconds', // 00-59
    a: 'AMPM', // am/pm
    A: 'AMPM', // AM/PM
  };

  // regex pattern to parse the format string '%X'
  this.regex = /(%\w)/g;

  // container for regex matches found in format string
  this.matches = [];

  // container to hold evaluated values
  this.dateFormatContainer = {};

  // object to hold the arrays of month formatting
  this.monthFormat = {};
  this.monthFormat['%F'] = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
  this.monthFormat['%M'] = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
  this.monthFormat['%m'] = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  this.monthFormat['%f'] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
};

DateFormat.prototype = {
  /**
   *
   * @param {string} format the returned format, '%f/%D/%y' returns 'Dec/31/69'
   * @param {string} date null value defaults to today, otherwise it can take input
   */
  format: function(format, date = null) {
    let fm, fn, value;
    // set format and get matches from the input string
    this.fmt = format;
    this.getMatches(this.fmt);
    this.date = date || new Date();

    // if the date object is null, get time now, otherwise get defined date param
    this.dObj = date === null ? new Date() : new Date(date);

    // loop through matches
    this.matches.forEach(m => {
      // remove the % and assign fn to the appropriate function
      fm = m.replace(/%/, '');

      // if match is 'A', need to call internal methods, rather than specific Date.prototype methods
      if (fm.toLowerCase() === 'a') {
        // define function
        fn = this.formats[fm];
        this.dateFormatContainer[m] = this[fn]();
      } else {
        fn = this.formats[fm];

        // ensure the date object is a function
        if (typeof this.dObj[fn] === 'function') {
          // get the value
          value = this.dObj[fn]();

          // check type and apply formatting
          if (m === '%m' || m === '%M' || m === '%f' || m === '%F') value = this.monthFormat[m][value];
          if (m === '%D' || m === '%H' || m === '%S' || m === '%i' || m === '%h') value = this.formatHoursPadStart(value);
          if (m === '%h') value = this.format12HourTime(value);

          // assign to container
          this.dateFormatContainer[m] = value;
        }
      }
    });

    // template replaces the matches in the format string w/ the key/value paris from dateFormatContainer
    return this.template();
  },
  /**
   * get all matches from the format stirng
   * @param {string} fmtString the format string
   */
  getMatches: function(fmtString) {
    while (true) {
      const match = this.regex.exec(fmtString);
      if (match === null) break;
      this.matches.push(match[0]);
    }
    return this;
  },
  /**
   * pad the start of the string w/ a 0 if the value is < 10
   * @param {int} value
   */
  formatHoursPadStart: function(value) {
    return value < 10 ? String(value).padStart(2, '0') : value;
  },
  /**
   * Default hour format is 24H, this returns 12H format
   * @param {int} value
   */
  format12HourTime: function(value) {
    if (value > 12) return value - 12;
    if (value === 0) return 12;
    return value;
  },
  /**
   * Insert KEY-VALUE pairs into the format string and return
   */
  template: function() {
    let tmpString = this.fmt;

    const keys = Object.keys(this.dateFormatContainer);

    keys.forEach(key => {
      const re = new RegExp(key, 'gm');
      tmpString = tmpString.replace(re, this.dateFormatContainer[key]);
    });

    return tmpString;
  },
  /**
   * Append the AM/PM
   */
  AMPM: function() {
    value = this.date.getHours();
    return value < 12 ? ' AM' : ' PM';
  },
};

// const d = new DateFormat();
// let b = d.format('%Y-%F-%D %H:%i:%S', '06-06-2019 05:00:15');
// const c = new DateFormat();
// let a = c.format('%H:%i:%S', new Date());
