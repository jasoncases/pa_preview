let uiSelector = document.createElement('template');

// vars

uiSelector.innerHTML = `
<style>
:host {
}
.container {
    background-color: hsla(0, 0%, 65%, 1);
    border-radius: 3px;
    display: flex;
    flex-direction: row;
    height: 44px;
    justify-content: space-between;
    position: relative;
    width: 230px;
    z-index: 0;
}
.data-node {
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    z-index: 2;
}
.slider {
    background-color: hsla(0, 0%, 85%, 1);
    border-radius: 2px;
    height: 40px;
    left: 0;
    margin: 2px 4px;
    position: absolute;
    top: 0;
    width: 48%;
    z-index: 1;
}
</style>

<div class="container" id="container">
    <div class="data-node">{false}</div>
    <div class="data-node">{true}</div>
    <div class="slider"></div>
</div>
`;

class UiSelect extends HTMLElement {
   constructor() {
      super();

      const shadowRoot = this.attachShadow({mode: 'open'});
      shadowRoot.appendChild(uiSelector.content.cloneNode(true));
      this.srPointer = shadowRoot;
   }
}

customElements.define('ui-select', UiSelect);
