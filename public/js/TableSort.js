const TableSort = function() {
   // config obj, keeps track of which id is being sorted and which direction, allowing for reverse sort
   this.sorted = {};
};

TableSort.prototype = {};

/**
 * Initialize all sorting processes
 */
TableSort.prototype.initSort = function() {
   this.initSortableRows();
   this.initColumnHeaders();
};
/**
 * Initialize the click event and config data handling.
 * Sort and Redraw all rows on click of header item
 */
TableSort.prototype.initColumnHeaders = function() {
   const headers = document.querySelectorAll('[id="sortableHeader"]');
   headers.forEach(header => {
      header.addEventListener('click', e => {
         this.idToSort = header.getAttribute('data-sort');
         console.log(this.idToSort);
         this.rows.sort((a, b) => this.sortRows(a, b));

         if (this.sorted.sortId === this.idToSort) {
            this.sorted.order = this.sorted.order === 'ASC' ? 'DESC' : 'ASC';
            if (this.sorted.order === 'DESC') {
               this.rows.reverse();
            }
         } else {
            this.sorted.sortId = this.idToSort;
            this.sorted.order = 'ASC';
         }

         this.redrawRows();
      });
   });
};
/**
 * Get value, check type, sort via type.
 */
TableSort.prototype.sortRows = function(a, b) {
   const valueA = this.getCellData(a, this.idToSort);
   const valueB = this.getCellData(b, this.idToSort);

   const type = valueA.substr(0, 1).match(/[0-9]/) ? 'number' : 'string';

   // behaves weird with mixed values, i.e., [CN-E, ALEXA, C200, 14-35, FS7]
   // TODO - need to clean this up to allow for mixed values

   if (type === 'string') {
      if (valueA.toLowerCase() === valueB.toLowerCase()) return 0;
      if (valueA.toLowerCase() < valueB.toLowerCase()) return -1;
      return 1;
   } else {
      return valueA - valueB;
   }
};
/**
 * gets the rows
 */
TableSort.prototype.initSortableRows = function() {
   this.rows = Array.from(document.querySelectorAll('[id="sortableRow"]'));
};
/**
 * Does the swaps
 */
TableSort.prototype.swapRows = function(a, b) {
   a.parentNode.insertBefore(b, a);
};
/**
 * Loops through rows and performs the swapping
 */
TableSort.prototype.redrawRows = function() {
   const length = this.rows.length;
   for (var ii = length - 1; ii > 0; ii--) {
      this.swapRows(this.rows[ii], this.rows[ii - 1]);
   }
};
/**
 * Acquire data in the cells to sort
 */
TableSort.prototype.getCellData = function(row, propName) {
   const sortTarget = this.determineTarget(row, propName);
   return Array.from(sortTarget).filter(child => {
      return child.id === propName;
   })[0].innerText;
};

/**
 * Checks the items within the target row and determines if a cell has the required data
 * If not, it assumes the data to sort is nested in the target row. Modify accordingly
 */
TableSort.prototype.determineTarget = function(row, propName) {
   const test = Array.from(row.children).filter(item => {
      if (item.id === propName) return item;
   });

   if (test.length === 0) {
      return row.children[0].children;
   } else {
      return row.children;
   }
};

/*
   TODO - UPDATE THESE STEPS FOR THE UPDATED PROTOTYPE
    How To Use:

    1.) Columns must be named 'sortableHeader' and have a data-sort tag with a value matching the id of the associated cell

    2.) Rows to be sorted must be named 'sortableRow' 

    3.) Cells must have an id matching the data-sort value of the associated column header

    4.) determineTarget allows for nested rows, if your data is contained within a secondary container row. The outer row will be sorted. 

    5.) Original iteration was an IIFE, but I'm converting most of my living codebase to prototype pattern for consistency
*/

/**
 * Implementation:
 *
 *  const TS = new TableSort();
 *  TS.initSort();
 *
 */
