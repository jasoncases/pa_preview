import {get} from '../../../Lib/Lib.js';
import {Upload} from './Upload.js';

/**
 * Control a progressbar component
 */
export class ProgressBar {
  container: HTMLElement;
  bar: HTMLElement;
  cancel: HTMLButtonElement;
  title: HTMLElement;

  Upload: Upload;

  /**
   * Set main container for the progress bar. The container should hold
   * at minimum an element with an id of 'progress-bar'. Other optional
   * elements are title, i.e. filename [progress-title] and a cancel btn
   * progress-cancel
   *
   * The passed attribute can be a string or an HTML element
   *
   * @param el string | element
   */
  public setContainer(el: any) {
    if (!el) {
      alert('No progress bar container set');
      return;
    }
    this.container = this._registerContainer(el);
    this._registerSubComponents();
    this._initListeners();
  }

  /**
   * Update the title container, if it exists w/ the passed filename
   *
   * @param filename
   */
  public setFilename(filename: string) {
    if (this.title) this.title.innerText = filename;
  }

  /**
   * Set the width of the bar in percentage 1-100
   *
   * @param percent
   */
  public setBarWidth(percent: number) {
    this.bar.style.width = `${percent}%`;
  }

  public show() {
    this.container.style.display = 'block';
  }

  public hide() {
    this.container.style.display = 'none';
  }

  public reset() {
    this.bar.style.width = '0px';
  }

  // BEGIN PRIVATE METHODS
  private _registerSubComponents() {
    if (!this.container)
      throw 'No container found ProgressBar._registerSubComponents';
    this.bar = this.container.querySelector('#progress-bar');
    this.cancel = this.container.querySelector('#progress-cancel');
    this.title = this.container.querySelector('#progress-title');
  }

  private _initListeners() {
    if (this.cancel)
      this.cancel.addEventListener('click', (e) => this.Upload.abort());
  }

  private _registerContainer(el: any) {
    return typeof el === 'string' ? <HTMLElement>get(el) : el;
  }
}
