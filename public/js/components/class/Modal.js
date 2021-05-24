const modalTemplate = document.createElement('template');
modalTemplate.innerHTML = `
<div class="modalContainer" id="modal-container">
    <div id="modal-close" class="modalClose"><i class="fas fa-times-circle"></i></div>
    <div id="modal-loader" style="color:hsla(195, 53%, 55%, 1);height:100%;width:100%;text-align:center;display:flex;flex-direction:column;justify-content:center;font-size:72px;"><i class="fas fa-cog fa-spin"></i></div>
</div>
`;

/**
 *
 * Define style via Modal::setStyle(styleObj)
 * Define target via Modal::setTarget(target)
 */
class Modal {
   constructor() {
      // set initial props
      // this.style = style;
      // this.target = typeof target === 'string' ? document.getElementById(target) : target;

      // state object
      this.state = {
         show: false,
      };

      /**
       * Overwrite this in extended children
       */
      this._config = {
         defaultStyle: {
            modalContainer: {
               position: 'absolute',
               padding: '8',
               backgroundColor: 'lightblue',
               border: '1px solid black',
               top: '500px',
               left: '500px',
               height: '300px',
               width: '300px',
               zIndex: 15000,
               overflow: 'auto',
               transition: 'all .3s ease-out',
               transformOrigin: '50% 50%',
               borderRadius: '5px',
               visibility: 'visible',
            },
            modalClose: {
               position: 'absolute',
               top: 0,
               right: 0,
               margin: '8px',
               fontSize: '14px',
            },
         },
         // importedStyle: {...this.style},
      };

      // we'll store one loaded view here
      // TODO - we'll expand this to allow us to cache via an identifier
      // TODO - need to test for state changes while maintaining
      this._cache = {
         view: '',
         url: '',
      };
   }
   setTarget(target) {
      this.target = typeof target === 'string' ? document.getElementById(target) : target;
   }
   setStyle(style) {
      this._config.importedStyle = {...style};
   }
   /**
    * Create the modal. If the cache URL is the same, it rehydrates from cache,
    * otherwise it runs an async load function to get the data
    *
    * TODO - expand the cache method to allow for multiple caches, reducing # of db hits
    *
    * @param {string} url           URL of the route to be loaded
    * @param {function} callback    callback to run once the data is loaded
    * @returns {void}
    */
   create(url, callback) {
      // check the cache
      if (this._cache.url === url) {
         this.rehydrateFromCache();
      } else {
         // if cache doesn't match, load fresh data
         this.load(url, callback);
      }
   }
   /**
    * Async call to load a route, method then caches the url, incoming data, and the callback for rehydration
    *
    * @param {string} url
    * @param {function} callback
    * @returns {void}
    */
   async load(url, callback) {
      this.append();
      this.revertToLoading();
      const request = await fetch(url);
      await request.text().then(data => {
         this.loadedData = data;
         this.runAtLoad();

         if (typeof callback === 'function') {
            callback();
         }

         this.cacheView(url, data, callback);
      });
   }

   /** *****************************************************
    * Acquire Modal Elements
    * *****************************************************/
   acquireLoader() {
      this.loader = document.getElementById('modal-loader');
   }
   acquireCloser() {
      this.closer = document.getElementById('modal-close');
   }
   acquireMainContainer() {
      this.container = document.getElementById('modal-container');
   }

   /** *****************************************************
    * Reappending Elements and Reverting State
    * *****************************************************/
   reappendCloser() {
      this.container.appendChild(this.closer);
   }
   revertToLoading() {
      this.container.innerHTML = '';
      this.container.appendChild(this.loader);
   }

   /** *****************************************************
    * Applying Styles To Elements
    * *****************************************************/
   applyStylesToCloser() {
      this.applyStyles(this.closer, this._config.defaultStyle.modalClose);
   }
   applyStylesToMainContainer() {
      this.applyStyles(this.container, this._config.defaultStyle.modalContainer);
   }
   applyUpdateStylesToMainContaier() {
      this.applyStyles(this.container, this._config.importedStyle.container);
   }
   injectView() {
      this.container.innerHTML = this.loadedData;
   }

   /** *****************************************************
    * Process Methods To Run At Set Times
    * * These methods collect other methods that need to
    * * run at specific times
    * *****************************************************/
   runAtLoad() {
      // once the data is loaded, set the loaded data to innerHTML of the container
      this.injectView();

      // apply loaded styles, allowing animation from loading to loaded
      this.applyUpdateStylesToMainContaier();

      // injectView overwrites the elements in container, so reappend the closer
      this.reappendCloser();

      // init listeners on the reappended elements
      this.initListeners();
   }
   runAtAppend() {
      this.acquireMainContainer();
      this.acquireCloser();
      this.acquireLoader();
      this.applyStylesToMainContainer();
      this.applyStylesToCloser();
      this.setState({show: true});
   }

   runAtRehydrate() {
      this.show();
      this.reappendCloser();
      this.applyUpdateStylesToMainContaier();
      this.applyStylesToCloser();
      this.setState({show: true});
   }

   /**
    * Append the template node to the target element, then run the collection method
    */
   append() {
      this.target.appendChild(modalTemplate.content.cloneNode(true));
      this.runAtAppend();
   }

   /** *****************************************************
    * Modification Methods
    * * These methods apply styles to elements, or change the
    * * state of the Modal component
    * *****************************************************/

   /**
    * Allows the user to send an object of camelCase formated props to an object and apply them as styles,
    * ex.: backgroundColor: 'white', borderRadius: '3px', etc
    * @param {element} el
    * @param {object} styleObj
    *
    * @return {void}
    */
   applyStyles(el, styleObj) {
      const keys = Object.keys(styleObj);
      keys.forEach(key => {
         const formattedAttr = key.replace(/[A-Z]/g, '-$&').toLowerCase();
         el.style[formattedAttr] = styleObj[key];
      });
   }

   /**
    * Hide the element and revert it to loading state. A call to reopen the modal
    * requires a reload of data, for now
    */
   hide() {
      // gatekeeper in case a hide gets called too many times
      if (this.state.show === false) return;

      // revert view to the loading view
      this.revertToLoading();

      // object with hide values
      const hideObj = {
         visibility: 'hidden',
         height: '300px',
         width: '300px',
      };

      // apply the hide values to the container element
      this.applyStyles(this.container, hideObj);

      // set state value
      this.setState({show: false});
   }
   show() {
      // gatekeep
      if (this.state.show === true) return;

      this.container.style.visibility = 'visible';

      // set state value
      this.setState({show: true});
   }

   /**
    * Send an object to update state values
    * @param {object} obj
    */
   setState(obj) {
      const keys = Object.keys(obj); // get keys from incoming object
      keys.forEach(key => {
         this.state[key] = obj[key]; // set each key val to obj.val
      });
   }

   /**
    * Listener container method
    */
   initListeners() {
      this.closer.addEventListener('click', e => {
         this.hide();
      });
   }

   /** *****************************************************
    * Caching and rehydrating methods
    * *****************************************************/

   /**
    *
    * @param {string} url           route to be loaded
    * @param {string} data          the view string of HTML loaded from the route
    * @param {function} callback    function sent to be run after view is loaded
    * @returns {void}
    */
   cacheView(url, data, callback) {
      //
      this._cache.url = url;
      this._cache.view = data;
      this._cache.callback = callback;
   }

   /**
    * Reset the view from the cache, run the container method and the callback
    */
   rehydrateFromCache() {
      this.container.innerHTML = this._cache.view;
      this.runAtRehydrate();
      if (typeof this._cache.callback === 'function') {
         this._cache.callback();
      }
   }

   /**
    *
    */
   clearCache() {
      this._cache.url = '';
      this._cache.view = '';
      this._cache.callback = '';
   }
}

export default Modal;
