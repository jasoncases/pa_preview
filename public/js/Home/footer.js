import { closest } from '/js/System/Lib/Lib.js'

( function () {

    /**
     * Set up script
     */
    function _init() {
        _initListeners()
    }

    /**
     * Set the listeners for the module.
     *
     * Currently, this module is handling the behavior of the many
     * 'details' elements used throughout Proaction, but it was set up
     * in such a way that would allow future developers to add other
     * default behavior. It was placed in the footer to allow for it to
     * be used throughout the site, on any pages that match the default
     * layout
     *
     */
    function _initListeners() {
        // capture clicks and touches
        window.addEventListener( 'click', ( e ) => captureEvent( e ) )
        window.addEventListener( 'touchstart', ( e ) => captureEvent( e ) )
    }

    /**
     * A routing method for any capture event.
     *
     * As stated above, currently it's just dealing with behavior from
     * window clicks and the 'details' html elements, but it remains
     * easily extendable.
     *
     * @param evt
     */
    function captureEvent( evt ) {
        _handleDetailElements( evt )
    }

    /**
     * All behavior for the 'details' elements contained here
     *
     * @param evt
     */
    function _handleDetailElements( evt ) {
        // System\Lib\Lib.closest recursively checks the DOM path of the
        // given element (first arg), to see if there is an element w/
        // given tagName, className, id, data-id (2nd arg). If a true
        // bool is given as 3rd arg, the recursive search will include
        // siblings at each branch upward
        const closestDetails = closest( evt.target, 'details' )
        // if there is an element in the target's path...
        if ( typeof closestDetails !== 'undefined' ) {
            // check that the element has a data-mode attribute
            if ( closestDetails.hasAttribute( 'data-mode' ) ) {
                return _setDetailMode( closestDetails )
            }
            // ... if there is no data-mode, fall through to default
        }
        // ... otherwise, fall through and close all details elements
        // this is desired default behavior, all other bahvior needs to
        // be assigned a data-mode and handled in the _setDetailMode
        // method below
        console.log( 'closestDetails is undfined...' )
        _closeDetailAll( evt )

    }

    /**
     * Switch through possbile data-mode behaviors
     *
     * @param detail
     */
    function _setDetailMode( detail ) {
        switch ( detail.getAttribute( 'data-mode' ) ) {
            case "close-siblings":
                return _closeDetailSiblings( detail )
            case "toggle":
                return _closeDetailSelf( detail )
        }
    }

    /**
     * Close all sibling elements, with the same data-id value
     *
     * @param detail
     */
    function _closeDetailSiblings( detail ) {
        // capture all detail elements
        const details = document.querySelectorAll( `[data-id="${ detail.getAttribute( 'data-id' ) }"]` )
        if ( !details ) return
        // loop through the found details
        details.forEach( d => {
            // if the given detail is NOT the ancestor we clicked on...
            if ( d !== detail ) {
                // ... remove the 'open' attribute
                d.removeAttribute( 'open' )
            }
        } )
    }

    /**
     * Close the given detail element.
     *
     * data-mode: 'toggle'
     *
     * The summary element of the detail component acts as toggle button
     * allowing the detail element to be open and closed by that one
     * button. Defaul behavior is overwritten, so 'toggle' details will
     * not close to outside window clicks.
     *
     * @param detail
     */
    function _closeDetailSelf( detail ) {
        console.log( 'close detail self' )
        return detail.removeAttribute( 'open' )
    }

    /**
     * Default 'details' behavior.
     *
     * All 'details' element will close to a window click, this includes
     * a self click. Until another data-mode is needed, if you need to
     * keep the element open on own clicks, 'close-siblings' will still
     * work
     *
     */
    function _closeDetailAll() {
        console.log( "close all..." )
        const details = document.querySelectorAll( 'details' )
        if ( !details ) return
        details.forEach( d => d.removeAttribute( 'open' ) )
    }

    // call the initialization method
    _init()
} )()
