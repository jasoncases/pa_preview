export class Refresher {
  _intervalTick: number = 3000; // 3 seconds
  _maxDisconnectDuration: number = 300; // 5 minutes
  _currentState: string; // 'active', 'disconnected'
  _cachedState: string; // 'active', 'disconnected'
  _currentStateDuration: number = 0;
  _lostConnectionTimestamp: number = 0;
  public constructor() {
    this._init();
  }

  private _init() {
    this._setInitialState();
    this._watch();
  }

  /**
   * Set initial state on page load
   */
  private _setInitialState() {
    this._setCurrentState();
    this._cachedState = this._currentState;
    this._lostConnectionTimestamp = this._now();
  }

  /**
   * Initialize interval to monitor the connection
   */
  private _watch() {
    setInterval(() => {
      this._monitorConnection();
    }, this._intervalTick);
  }

  /**
   * If state changes, handle with the logic in this method
   */
  private _handleChangeOfState() {
    console.warn(
      `:: Connection state has changed, current state: ${this._currentState}.`,
    );
    if (this._currentState === 'disconnected') {
      // update the lostConnectionTimestamp to get a start time
      this._lostConnectionTimestamp = this._now();
    } else {
      // At this point, we have returned to an active connection
      // and if enough time has elapsed, we want to reload this same page
      if (this._currentStateDuration > this._maxDisconnectDuration) {
        console.log(
          ':: Refreshing page to prevent stale state issues: ',
          location.href,
        );
        this._refreshTheCurrentPage();
      } else {
        // if not enough time has passed, just reset the duration count
        this._resetCurrentStateDuration();
      }
    }
  }

  private _resetCurrentStateDuration() {
    this._currentStateDuration = 0;
  }

  private _monitorConnection() {
    if (this._thereHasBeenAStateChange()) {
      this._handleChangeOfState();
    }
    this._setCurrentStateDuration();
    this._setCurrentState();
  }

  private _setCurrentStateDuration() {
    this._currentStateDuration = this._now() - this._lostConnectionTimestamp;
  }

  private _setCurrentState() {
    this._cachedState = this._currentState;
    this._currentState = navigator.onLine ? 'active' : 'disconnected';
  }

  private _thereHasBeenAStateChange() {
    return this._currentState !== this._cachedState;
  }

  private _now() {
    return Math.round(new Date().getTime() / 1000);
  }

  private _refreshTheCurrentPage() {
    location = location;
  }
}
