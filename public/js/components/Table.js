const tableContainer = document.createElement('div');
const style = document.createElement('template');
style.innerHTML = `
<style>
.table {
    border: 1px solid #1C6EA4;
    background-color: #EEEEEE;
    width: 100%;
    text-align: center;
    border-collapse: collapse;
  }
  .table td, .table th {
    border: 1px solid #AAAAAA;
    padding: 3px 2px;
  }
  .table tbody td {
    font-size: 13px;
  }
  .table tr:nth-child(even) {
    background: #D0E4F5;
  }
  .table thead {
    background: #1C6EA4;
    background: -moz-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
    background: -webkit-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
    background: linear-gradient(to bottom, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
    border-bottom: 2px solid #444444;
  }
  .table thead th {
    font-size: 15px;
    font-weight: bold;
    color: #FFFFFF;
    text-align: center;
    border-left: 2px solid #D0E4F5;
  }
  .table thead th:first-child {
    border-left: none;
  }
  
  .table tfoot {
    font-size: 14px;
    font-weight: bold;
    color: #FFFFFF;
    background: #D0E4F5;
    background: -moz-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
    background: -webkit-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
    background: linear-gradient(to bottom, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
    border-top: 2px solid #444444;
  }
  .table tfoot td {
    font-size: 14px;
  }
  .table tfoot .links {
    text-align: right;
  }
  .table tfoot .links a{
    display: inline-block;
    background: #1C6EA4;
    color: #FFFFFF;
    padding: 2px 8px;
    border-radius: 5px;
  }
</style>`;


class Table extends HTMLElement {

    get src() {
        return this.hasAttribute("src") ? this.getAttribute("src") : "Please add a url pointing to your table data.";
    }

    constructor() {
        super(); //thanks for askin!

        bindTable(this.src);

        const shadowRoot = this.attachShadow({mode: 'open'});
        shadowRoot.appendChild(style.content.cloneNode(true));
        shadowRoot.appendChild(tableContainer);

    }
}

let apidata = {}

async function getData(url) {
    const response = await fetch(url);
    const jsonResponse = await response.json();
    apidata = JSON.stringify(jsonResponse);
}

async function bindTable(url) {

    await getData(url)

    var data = JSON.parse(apidata);

    var col = [];


    for (var i = 0; i < data.length; i++) {
        for (var key in data[i]) {
            if (col.indexOf(key) === -1) {
                col.push(key);
            }
        }
    }

    var table = document.createElement("table");
    table.setAttribute("class", "table");
    var tr = table.insertRow(-1);

    for (var i = 0; i < col.length; i++) {
        var th = document.createElement("th");
        th.innerHTML = col[i];
        tr.appendChild(th);
    }

    for (var i = 0; i < data.length; i++) {

        tr = table.insertRow(-1);

        for (var j = 0; j < col.length; j++) {
            var tabCell = tr.insertCell(-1);
            tabCell.innerHTML = data[i][col[j]];
        }
    }

    tableContainer.appendChild(table);

}

export default Table;