//helloworld.js
let template = document.createElement('template');
template.innerHTML = `
<style>
:host {
}
</style>
<div>Hello World.</div>`;

class HelloWorld extends HTMLElement {
  constructor() {
    super();

    const shadowRoot = this.attachShadow({ mode: 'open' });

    console.log('template: ', template);

    // append the template element above w/ cloneNode
    shadowRoot.appendChild(template.content.cloneNode(true));

    this.srPointer = shadowRoot;
  }
}

export default HelloWorld;
