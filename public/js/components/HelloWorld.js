//example:   <hello-world planet="Jupiter"></hello-world>
//example in html page:   <hello-world planet="Jupiter"></hello-world>
//then register this in globalComponents.js

let template = document.createElement('template');
template.innerHTML = "Hello [[planet]]";

class HelloWorld extends HTMLElement {
    
    constructor() {
        super(); //thanks for askin!

        const shadowRoot = this.attachShadow({mode: 'open'});

        const data = this.getAttribute("planet");
        template.innerHTML = template.innerHTML.replace('[[planet]]', data);

        shadowRoot.appendChild(template.content.cloneNode(true));
    }
}

export default HelloWorld;