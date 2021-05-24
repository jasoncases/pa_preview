import { PinButtonConfig, PinButton } from './Button.js';

export class ButtonFactory {
  public static createButton(btnObj: PinButtonConfig) {
    return new PinButton(btnObj);
  }
}
