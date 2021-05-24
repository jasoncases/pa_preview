class Diff {
  constructor(targetContainer) {
    //
    this.target = targetContainer;
    this.outputInstance = [];
    this.virtualInstance = [];
    this.cache = [];

    console.log('target: ', this.target);
  }

  init() {
    this.initPrimaryInstance();
    this.initCache();
    this.tick();
  }
  initPrimaryInstance() {
    this.virtualInstance.length = 0;
    const clone = Array.from(this.target.children);
    this.virtualInstance = clone;
  }
  initOutputInstance() {}
  initCache() {
    console.log('initCache Called');
    this.cache.length = 0;
    const clone = Array.from(this.target.children);

    this.cache = clone;
  }
  overwriteCache() {
    console.log('WERE OVERWRITING THE CACHE');
    this.cache.length = 0;
    this.cache = this.cloneInstance(this.virtualInstance);
  }
  cloneInstance(array) {
    return Array.from(JSON.parse(JSON.stringify(array)));
  }
  redrawOutput() {
    console.log('REDRAW OUTPUT CALLED');
  }
  compareVirtualInstanceToCache() {
    const elementsToUpdate = this.cache.filter(el => {
      return this.compareElement(el.id);
    });
  }
  compareElement(id) {
    const _virtualElement = this.virtualInstance.filter(viEl => {
      return viEl.id === id;
    })[0];

    const _cacheElement = this.cache.filter(caEl => {
      return caEl.id === id;
    })[0];

    console.log('_virtualElement:', _virtualElement);
    console.log('_cacheElement:', _cacheElement);
    if (JSON.stringify(_virtualElement.innerHTML) !== JSON.stringify(_cacheElement.innerHTML)) {
      console.log('ELEMENTS ARE NOT THE SAME');
      return true;
    }

    if (_virtualElement.outerHTML !== _virtualElement.outerHTML) {
      console.log('OUTERHTML IS DIFFERENT');
      return true;
    }

    return false;
  }
  compare() {
    //
    this.initPrimaryInstance();

    const diff = this.compareVirtualInstanceToCache();
    console.log('diff:', diff);
    if (diff) {
      this.redrawOutput();
      this.overwriteCache();
    }
  }
  tick() {
    this.compare();
    setTimeout(() => {
      this.tick();
      console.log('VI: ', this.virtualInstance);
      console.log('Cache: ', this.cache);
    }, 5000);
  }
}

export default Diff;
