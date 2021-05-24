/**
 * Library of *EDITABLE* component properties.
 * Some props remain static, but anything that may need to be adjusted (usually design related)
 * goes in this file as an object.
 *
 * This library is exported as a const value 'componentProps' which is then imported at the top
 * of each component file. Once in the componenet file, the values are aliased for brevity, i.e.:
 *          * var check = componentProps.Checkbox
 *
 *
 * The components themselves are then exported to a globalProperties.js module file which defines
 * the element via customElements.define()
 *
 * Naming conventions:
 * 1.) Top level objects map to their respective components, i.e. this.Checkbox => Checkbox.js.
 *      these MUST BE PascalCase
 * 2.) The properties contained within top level objects SHOULD BE camelCase and correspond to
 *      a clearly delineated element within the template of the component, i.e.,
 *          * this.Checkbox.sliderContainer maps to <div id="slider-container"></div>
 * 3.) If a proper controls a modifiable element, the property MUST have a prefixed underscore
 *      and a signifying word for clarity, i.e.,
 *          * this.Checkbox._leftPos_sliderContainer updates the margin of the slider-container
 *          * element when the component is supplied with a left position modidification, (defined
 *          * elsewhere in the Component code)
 * 4.) Give details below the Top Level object, ex filename, classnae, html-tag, brief description
 */
const ComponentPropsLibary = function() {
  //

  this.Checkbox = {
    /**
     * filename: Checkbox.js
     * classname: uiCheck
     * html-tag: ui-checkbox
     * formatting attributes: label, background, labelPosition
     * watched attributes: checked, disabled
     *
     * Description:
     * ui slider element, named checkbox because the HTML checkbox hack is most
     * frequently used to spoof it via css trickery
     */
    label: {
      fontSize: 14,
      fontWeight: 'bold',
      fontColor: 'hsla(0, 0%, 25%, 1)',
      letterSpacing: 1,
      paddingTop: 0,
      paddingBottom: 0,
      paddingLeft: 0,
      paddingRight: 0,
    },
    labelContainer: {
      marginLeft: 30,
    },
    sliderContainer: {
      marginLeft: 4,
      background: 'hsla(0, 0%, 85%, 1)',
      borderRadius: 17,
      height: 30,
      width: 50,
    },
    valueText: {
      fontColor: 'hsla(0, 0%, 45%, 1)',
      textTransform: 'uppercase',
      fontWeight: 'bold',
      letterSpacing: 1,
      fontSize: 12,
    },
    slider: {
      background: 'hsla(0, 0%, 98%, 1)',
      borderRadius: 50,
      height: 24,
      width: 24,
      activeBackground: 'hsla(220, 80%, 45%, 1)',
    },
    animation: {
      speed: 0.2,
    },
    _leftPos_label: {
      marginLeft: 0,
    },
    _leftPos_sliderContainer: {
      marginLeft: `30px`,
    },
  };

  this.TrioSelector = {
    /**
     * filename: TrioSelector.js
     * classname: TrioSelect
     * html-tag: trio-select
     * formatting attributes: label, left, right, middle
     * watched attributes: value, disabled
     *
     *
     * Description:
     * Larger slider element with internal text nodes
     */
    label: {
      fontSize: 14,
      fontWeight: 'bold',
      fontColor: 'hsla(0, 0%, 25%, 1)',
      letterSpacing: 1,
      paddingTop: 3,
      paddingBottom: 3,
      paddingLeft: 0,
      paddingRight: 0,
    },
    sliderContainer: {
      marginLeft: 4,
      background: 'hsla(0, 0%, 85%, 1)',
      borderRadius: 5,
      height: 44,
      width: 420,
    },
    valueText: {
      fontColor: 'hsla(0, 0%, 45%, 1)',
      textTransform: 'uppercase',
      fontWeight: '700',
      letterSpacing: 1,
      fontSize: 12,
    },
    slider: {
      background: 'hsla(0, 0%, 98%, 1)',
      borderRadius: 5,
      height: 40,
      width: 135,
    },
    animation: {
      speed: 0.2,
    },
  };

  this.BoolSelector = {
    /**
     * filename: BoolSelector.js
     * classname: BoolSelect
     * html-tag: bool-select
     * formatting attributes: label, left, right
     * watched attributes: checked, disabled
     *
     *
     * Description:
     * Larger slider element with internal text nodes
     */
    label: {
      fontSize: 14,
      fontWeight: 'bold',
      fontColor: 'hsla(0, 0%, 25%, 1)',
      letterSpacing: 1,
      paddingTop: 3,
      paddingBottom: 3,
      paddingLeft: 0,
      paddingRight: 0,
    },
    sliderContainer: {
      marginLeft: 4,
      background: 'hsla(0, 0%, 85%, 1)',
      borderRadius: 5,
      height: 44,
      width: 280,
    },
    valueText: {
      fontColor: 'hsla(0, 0%, 45%, 1)',
      textTransform: 'uppercase',
      fontWeight: '700',
      letterSpacing: 1,
      fontSize: 12,
    },
    slider: {
      background: 'hsla(0, 0%, 98%, 1)',
      borderRadius: 5,
      height: 40,
      width: 135,
    },
    animation: {
      speed: 0.2,
    },
  };

  this.ColorPicker = {};
};

/**
 * instantiate and export the alias, rather than having to alias at each file
 */
const componentProps = new ComponentPropsLibary();

export const check = componentProps.Checkbox;
export const bool = componentProps.BoolSelector;
export const trio = componentProps.TrioSelector;
export const cp = componentProps.ColorPicker;

// TODO - testing whether it's necessary to keep these as one prototype or spit them.
// export {check, bool, trio};
