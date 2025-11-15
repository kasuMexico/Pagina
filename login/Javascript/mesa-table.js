// /login/Javascript/mesa-table.js
(function () {
  function hydrateTable(table) {
    let headers = [];
    const thead = table.querySelector('thead');

    if (thead) {
      const cols = thead.querySelectorAll('th');
      headers = Array.from(cols).map((th) => th.textContent.trim());
    } else {
      const firstRow = table.querySelector('tr');
      if (firstRow) {
        headers = Array.from(firstRow.children).map((cell) => cell.textContent.trim());
      }
    }

    const bodyRows = table.querySelectorAll('tbody tr');
    bodyRows.forEach((row) => {
      Array.from(row.cells).forEach((cell, idx) => {
        if (!cell.getAttribute('data-label') && headers[idx]) {
          cell.setAttribute('data-label', headers[idx]);
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mesa-table').forEach(hydrateTable);
  });
})();
