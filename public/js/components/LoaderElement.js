let loader = document.createElement('template');

let loadState = document.createElement('template');
let failState = document.createElement('template');
let successState = document.createElement('template');

var width = 125;
var height = 125;
var borderRadius = 5;
var background = 'hsl(0, 0%, 93%)';
var borderColor = 'hsl(0, 0%, 63%)';

var radius = height * 0.25;
var fillOrigin = `hsla(200, 75%, 60%, 0)`;
var fill = `hsla(200, 75%, 60%, 1)`;
var strokeColor = 'hsl(200, 75%, 40%)';
var strokeWidth = 3;

var failFillOrigin = `hsla(200, 75%, 60%, 0)`;
var failFill = `hsla(0, 60%, 80%, 1)`;
var failStrokeColor = `hsla(0, 0%, 0%, 1)`;
var failStrokeWidth = 3;

loader.innerHTML = `
<style> 
:host {
    position: absolute;
    height: 100%;
    width: 100%;
}
.container {
    position: absolute;
    height: ${height}px;
    width: ${width}px;
    top: calc(50% - ${height / 2}px);
    left: calc(50% - ${width / 2}px);
    border-radius: ${borderRadius}px;
    background-color: ${background};
    border: 1px solid ${borderColor};
    box-shadow: 0px 1px hsla(0, 0%, 0%, 0.25), 1px 4px hsla(0, 0%, 0%, 0.15);
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    align-content: center;
    z-index: 10000;
}
.success_checkmark_circle {
    stroke-dasharray: 325;
    stroke-dashoffset: 325;
    stroke-width: ${strokeWidth};
    stroke-miterlimit: 10;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    stroke: ${strokeColor};
    fill: none;
    animation: stroke .4s ease-in forwards;
}
.success_checkmark_fill {
    fill: ${fill};
}
.success_checkmark_overlay {
    r: ${radius};
    animation: fill .23s ease-in-out .4s forwards;
}
@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}
@keyframes fill {
    100% {
        r: 0;
    }
}
@keyframes fill2 {
    100% {
        r: 0;
    }
}
@keyframes fadeout {
    0% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}
.success_checkmark_check {
    transform-origin: 50% 50%;
    stroke-dasharray: 200;
    stroke-dashoffset: 200;
    animation: stroke .3s ease-in .8s forwards;
}
.checkmark {
    font-size: ${Math.round(height * 0.35)}px;
    color: ${background};
    position: absolute;
    margin: 0 auto;
}
.fadeout {
    animation: fadeout .s ease-in forwards;
}


/* BEGIN: SPINNER CSS */
.lds-spinner {
  color: official;
  display: inline-block;
  position: relative;
  width: 64px;
  height: 64px;
}
.lds-spinner div {
  transform-origin: 32px 32px;
  animation: lds-spinner 1.2s linear infinite;
}
.lds-spinner div:after {
  content: " ";
  display: block;
  position: absolute;
  top: 3px;
  left: 29px;
  width: 5px;
  height: 14px;
  border-radius: 20%;
  background: rgb(22, 60, 78);
}
.lds-spinner div:nth-child(1) {
  transform: rotate(0deg);
  animation-delay: -1.1s;
}
.lds-spinner div:nth-child(2) {
  transform: rotate(30deg);
  animation-delay: -1s;
}
.lds-spinner div:nth-child(3) {
  transform: rotate(60deg);
  animation-delay: -0.9s;
}
.lds-spinner div:nth-child(4) {
  transform: rotate(90deg);
  animation-delay: -0.8s;
}
.lds-spinner div:nth-child(5) {
  transform: rotate(120deg);
  animation-delay: -0.7s;
}
.lds-spinner div:nth-child(6) {
  transform: rotate(150deg);
  animation-delay: -0.6s;
}
.lds-spinner div:nth-child(7) {
  transform: rotate(180deg);
  animation-delay: -0.5s;
}
.lds-spinner div:nth-child(8) {
  transform: rotate(210deg);
  animation-delay: -0.4s;
}
.lds-spinner div:nth-child(9) {
  transform: rotate(240deg);
  animation-delay: -0.3s;
}
.lds-spinner div:nth-child(10) {
  transform: rotate(270deg);
  animation-delay: -0.2s;
}
.lds-spinner div:nth-child(11) {
  transform: rotate(300deg);
  animation-delay: -0.1s;
}
.lds-spinner div:nth-child(12) {
  transform: rotate(330deg);
  animation-delay: 0s;
}
@keyframes lds-spinner {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}
/* END: SPINNER CSS */

// -----------------------------------------
.fail_checkmark_circle {
  stroke-dasharray: ${radius * Math.PI + 10};
  stroke-dashoffset: ${radius * Math.PI + 10};
  stroke-width: ${failStrokeWidth};
  stroke-miterlimit: 10;
  transform: rotate(-90deg);
  transform-origin: 50% 50%;
  stroke: ${failStrokeColor};
  fill: none;
  animation: stroke .4s ease-in forwards;
}
.fail_checkmark_fill {
  fill: ${failFill};
}
.fail_checkmark_overlay {
  r: ${radius};
  animation: fill .23s ease-in-out .4s forwards;
}
.fail_checkmark_check {
  transform-origin: 50% 50%;
  stroke-dasharray: 200;
  stroke-dashoffset: 200;
  animation: stroke .3s ease-in .8s forwards;
}

</style>
<div class="container" id="loader-container">
  
</div>
`;

successState.innerHTML = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${height} ${width}">
  <circle class="success_checkmark_fill" cx="${width / 2}" cy="${height / 2}" r="${radius}" />
  <circle class="success_checkmark_overlay" cx="${width / 2}" cy="${height / 2}" r="${radius}" fill="${background}" />
  <circle class="success_checkmark_circle" cx="${width / 2}" cy="${height / 2}" r="${radius}" fill="${fill}" />
</svg>
<span id="checkmark" class="checkmark">&#10003;</span>
`;

loadState.innerHTML = `
<div class="spinner-container" id="loader">
  <div class="lds-spinner">
    <div></div>
    <div></div>
    <div></div>
    <div></div> 
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
  </div>
</div>
`;

failState.innerHTML = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${height} ${width}">
  <circle class="fail_checkmark_fill" cx="${width / 2}" cy="${height / 2}" r="${radius}" />
  <circle class="fail_checkmark_overlay" cx="${width / 2}" cy="${height / 2}" r="${radius}" fill="${background}" />
  <circle class="fail_checkmark_circle" cx="${width / 2}" cy="${height / 2}" r="${radius}" fill="${failFill}" />
</svg>
<span id="checkmark" class="checkmark">&#10003;</span>
`;

export class LoaderElement extends HTMLElement {
   static get observedAttributes() {
      return ['state', 'disabled'];
   }
   get state() {
      return this.getAttribute('state') || 'load';
   }
   set state(val) {
      if (val) {
         this.setAttribute('state', val);
      } else {
         this.removeAttribute('state');
      }
   }
   constructor() {
      super();

      const shadowRoot = this.attachShadow({mode: 'open'});
      shadowRoot.appendChild(loader.content.cloneNode(true));
      this.sr = shadowRoot;
      this.container = shadowRoot.getElementById('loader-container');

      this.loadState = loadState.innerHTML;
      this.successState = successState.innerHTML;

      this.successAnimationLength = 1500;
      this.failState = failState.innerHTML;
      this.setState(this.state);
      // this.updateState();
   }

   attributeChangedCallback(name, oldValue, newValue) {
      // check the name and run your desired method
      if (name === 'state') {
         //  this.setState(this.state);
      } else {
         // TODO - change style on disabled
      }
   }
   connectedCallback() {
      // this.hide();
   }

   animateCheckMark() {
      setTimeout(() => {
         const checkmark = this.sr.getElementById('checkmark');
         checkmark.style.top = `${height / 2}px`;
         checkmark.style.marginTop = `-${height * 0.21}px`;
         checkmark.style.left = '50%';
         checkmark.style.marginLeft = `-${height * 0.125}px`;
         //  this.animateFadeOut();
      }, 1000);
   }

   animateFadeOut() {
      setTimeout(() => {
         this.container.classList.add('fadeout');
      }, 300);
   }

   setState(state, removeTimer = null) {
      console.log('state:', state);
      this.show();
      this.state = state;
      this.updateState(removeTimer);
   }

   updateState(removeTimer) {
      this.container.innerHTML = this[`${this.state}State`];
      if (this.state === 'success') {
         this.animateCheckMark();
         console.log('hide called from LoaderElement::updateState()');
         this.hide(this.successAnimationLength);
      }

      if (removeTimer === null) return;
      console.log('hide called from LoaderElement::updateState() behind removeTimer');
      this.hide(removeTimer);
   }

   hide(delay) {
      console.log('LoaderElement::hide() is called');
      if (delay === undefined) delay = 0;
      setTimeout(() => {
         this.setVisibility('hidden');
      }, delay);
   }
   show() {
      this.setVisibility('visible');
   }
   setVisibility(val) {
      this.container.style.visibility = val;
   }
   move(xyObj) {
      //
      this.container.style.top = `${xyObj.y}px`;
      this.container.style.left = `${xyObj.x}px`;
   }
}

export default LoaderElement;

/*

  
</style>
<div class="container">

</div>
*/
