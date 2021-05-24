interface CreatorInterface {
  //
  _config: ConfigInterface;
  elementAttributes: Object;
  el: HTMLElement;
  svg: string;

  // methods
  applyElementLevelAttributes(el: HTMLElement, elementAttributes: Object, svg?: string): void;
  applyStyleAttributes(el: HTMLElement, styleAttributes: Object, svg?: string): void;
  applyAttributes(el: HTMLElement, attrs: Object, svg?: string): void;
  createElement(tag: string, attributes: Object, svg?: string, callback?: Function): HTMLElement;
  createNewElement(tag: string, attributes: Object, callback?: Function): HTMLElement;
  createSvgElement(tag: string, attributes: Object, callback?: Function): HTMLElement;
}

interface ConfigInterface {
  svgns: string;
}

export class Creator implements CreatorInterface {
  _config: ConfigInterface;
  elementAttributes: Object;
  el: HTMLElement;
  svg: string;
  constructor() {
    //
    this._config = {
      svgns: <string>'http://www.w3.org/2000/svg',
    };
  }

  /**
   * Apply top level attributes, i.e., id, classList, etc to provided element
   * @param {*} el
   * @param {*} elementAttributes
   */
  applyElementLevelAttributes(el: HTMLElement, elementAttributes: Object, svg: string = null): void {
    // get the keys
    const attrKeys = Object.keys(elementAttributes);

    // loop through
    attrKeys.forEach(key => {
      // format the camelCase to hyphenated => 'camel-case'
      if (svg === 'svg') {
        el.setAttributeNS(null, key, elementAttributes[key]);
      } else {
        el.setAttribute(key, elementAttributes[key]);
      }
    });
  }
  /**
   * Apply style level attributes to an element
   * @param {*} el
   * @param {*} styleAttributes
   * @param {*} svg
   */
  applyStyleAttributes(el: HTMLElement, styleAttributes: Object, svg: string = null) {
    // get the keys
    const attrKeys = Object.keys(styleAttributes);

    // loop through
    attrKeys.forEach(key => {
      // format the camelCase to hyphenated => 'camel-case'
      const formattedAttr = key.replace(/[A-Z]/g, '-$&').toLowerCase();
      if (svg === 'svg') {
        el.setAttributeNS(null, formattedAttr, styleAttributes[key]);
      } else {
        el.style[formattedAttr] = styleAttributes[key];
      }
    });
  }
  applyAttributes(el: HTMLElement, attrs: Object, svg: string = null) {
    const attributeContainer = {};
    // blacklist non-style related props within the Object and pull them out.
    const attrBlacklist = ['id', 'class', 'repeatCount', 'attributeName', 'attributeType'];
    // Pull any attributes from the blacklist
    const attrKeys = Object.keys(attrs).filter(attr => {
      return attrBlacklist.indexOf(attr) >= 0;
    });

    // if any are found, loop through and call the element level method
    if (attrKeys.length > 0) {
      attrKeys.forEach(key => {
        attributeContainer[key] = attrs[key];
      });
      this.applyElementLevelAttributes(el, attributeContainer, svg);
    }
    // Pull all keys out that are style level, being anything not in the blacklist
    const styleKeys = Object.keys(attrs).filter(attr => {
      return attrBlacklist.indexOf(attr) < 0;
    });

    // If any are found, push them and their corresponding values to a container obj and call the style method
    if (styleKeys.length > 0) {
      const styleAttributesContainer = {};
      styleKeys.forEach(key => {
        styleAttributesContainer[key] = attrs[key];
      });
      this.applyStyleAttributes(el, styleAttributesContainer, svg);
    }
  }
  /**
   * Core.createElement replaces document.createElement and document.createElementNS, allows pushing
   * attributes as well as any necessary call backs. Event listeners should be added added after
   * the element is created and not as a callback.
   *
   * @param {string} tag - html/svg tagname
   * @param {Object} attributes - an Object of attributes. hyphenated attributes must be entered as camelCase, i.e., backgroundColor.
   *                              The subsequent methods parse them via regex/replace to background-color format. Keywords like classList
   *                              and id are removed and applied to the element appropriately
   * @param {string} svg - string, 'svg' to create an svg element, any other value will result in a regular html element being created.
   * @param {function} callback - any callback contingient on element creation
   * @return {element} - returns the element that can then be appended and/or have event listeners applied.
   */
  createElement(tag: string, attributes: Object, svg: string = null, callback: Function = null) {
    // if svg is a function, swap the variables and set svg to null
    if (typeof svg === 'function') {
      callback = svg;
      svg = null;
    }
    if (svg === 'svg') {
      return this.createSvgElement(tag, attributes, callback);
    } else {
      return this.createNewElement(tag, attributes, callback);
    }
  }
  /**
   *
   *
   * @param {*} tag
   * @param {*} attributes
   * @param {*} callback
   */
  createNewElement(tag: string, attributes: Object, callback: Function = null) {
    //
    const el = document.createElement(tag);
    this.applyAttributes(el, attributes);

    if (typeof callback === 'function') {
      callback();
    }

    return el;
  }
  /**
   *
   * @param {*} tag
   * @param {*} attributes
   * @param {*} callback
   */
  createSvgElement(tag: string, attributes: Object, callback: Function = null) {
    //
    const el: any = document.createElementNS(this._config.svgns, tag);
    this.applyAttributes(el, attributes, 'svg');

    if (typeof callback === 'function') {
      callback();
    }

    return el;
  }
}

export default Creator;
