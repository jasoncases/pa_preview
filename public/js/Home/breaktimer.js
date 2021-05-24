// BEGIN SCRIPT FOR BREAK TIMERS
import { get } from '/js/System/Lib/Lib.js'
import { Fetch } from '/js/System/Components/Fetch/Fetch.js'
import { User } from '/js/User/src/User.js'

/**
 * Module that listens to User for changes and if the current user
 * enters a break or lunch state
 * */
export const Timer = ( () => {
  const yellow = '#ECE932'
  const orange = '#FF9100'
  const bgTimeOutClr = '#e94444'
  let timer, bgColor, status, max, duration, timeReference
  let targetElement, containerId
  const onBreakDisplay = document.querySelector( '.onBreakDisplay' )


  function setProps() {
    timer = get( 'breaktimer-countdown' )
    status = parseInt( timer.getAttribute( 'data-status' ) )
    bgColor = Math.abs( status ) === 3 ? orange : yellow
    max = parseInt( timer.getAttribute( 'data-max' ) )
    duration = parseInt( timer.getAttribute( 'data-duration' ) )
    timeReference = max - duration
  }

  function remove() {
    const el = get( 'breaktimer-container' )
    if ( !el ) return
    el.remove()
    onBreakDisplay.style.display = 'block'
  }

  async function load( id ) {
    return Fetch.get(
      `/module/jasoncases_breaktimer/${ id }`,
      {},
      { html: 'HTML' }
    )
  }

  function update() {
    try {
      const actionIds = [ 3, 5 ]
      const status = User.currentStatus()
      if ( actionIds.indexOf( status.activityId ) === -1 ) {
        return remove()
      }

      load( User.get( 'employeeId' ) ).then( ( res ) => {
        if ( res.status != 'success' ) throw `Problem loading data`
        createTimer( res.data )
        setProps()
        if ( onBreakDisplay ) onBreakDisplay.style.display = 'none'
        build()
      } )
    } catch ( e ) {
      console.error( `Error: ${ e }` )
    }
  }

  function createTimer( html ) {
    if ( !containerId ) throw 'No target containerId set'
    const target = get( containerId )
    if ( !target ) throw 'No target container found'
    const firstElement = target.firstElementChild
    if ( !firstElement ) {
      return target.appendChild( createTimerNode( html ) )
    }
    return target.insertBefore( createTimerNode( html ), firstElement )
  }

  function createTimerNode( html ) {
    const div = document.createElement( 'div' )
    div.innerHTML = html
    div.id = 'breaktimer-container'
    return div
  }

  function build() {
    $( `#breaktimer-countdown` )
      .TimeCircles( {
        ref_date: Date.now() + timeReference * 1000,
        start: true,
        refresh_interval: 0.1,
        count_past_zero: false,
        circle_bg_color: '#60686F',
        use_background: true,
        fg_width: 0.04,
        bg_width: 1.6,
        time: {
          Days: { show: false, text: 'Days', color: '#F09217' },
          Hours: { show: false, text: 'Hours', color: '#F09217' },
          Minutes: { show: true, text: 'Minutes', color: bgColor },
          Seconds: { show: true, text: 'Seconds', color: bgColor },
        },
      } )
      .rebuild()
    $( '#breaktimer-countdown' ).TimeCircles().addListener( timesUp )
  }
  function timesUp( unit, value, total ) {
    if ( total < 60 && total > 45 ) {
      $( this ).addClass( 'alert-1' )
    } else if ( total < 45 && total > 30 ) {
      $( this ).removeClass( 'alert-1' )
      $( this ).addClass( 'alert-2' )
    } else if ( total < 30 && total > 15 ) {
      $( this ).removeClass( 'alert-2' )
      $( this ).addClass( 'alert-3' )
    } else if ( total < 15 ) {
      $( this ).removeClass( 'alert-3' )
      $( this ).addClass( 'alert-4' )
    }
    // if (total === 0) {
    //   $(this).addClass('alert-3')
    // }
  }

  function setContainerId( id ) {
    containerId = id
  }

  return {
    update: update,
    setContainerId: setContainerId,
  }
} )()
