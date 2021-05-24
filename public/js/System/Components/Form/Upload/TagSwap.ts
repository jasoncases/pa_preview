export class TagSwap {
  re: RegExp = /(\@\[.*\])/gm;

  public get(string) {
    return string.match(new RegExp(this.re));
  }
}
