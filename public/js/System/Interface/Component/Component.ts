import { RuntimeConfigurationObject } from '../../Lib/Lib.js';
import { HeaderObjectInterface } from '../../Lib/MinorInterfaces.js';

export interface ComponentInterface {
  // private props
  _id: string;

  // element pointers
  _element: HTMLElement;
  _parent: HTMLElement;
  _target: HTMLElement;

  // public methods
  init(): void;
  registerTargetElementByParent(el: HTMLElement): void;
  getElement(): HTMLElement;
  getParent(): HTMLElement;
  getTarget(): HTMLElement;
  getId(): string;
}

export interface FetchOptions {
  mode: string;
  headers: HeaderObjectInterface;
  body: string;
}
