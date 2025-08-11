// app.js — exports + clickable rows (no dark mode code)

// ====== Export helpers (CSV, XLSX, PDF) from #booksTable ======
function tableTo2DArray(table){
  const rows=[...table.querySelectorAll('tr')];
  return rows.map(r=>[...r.children].map(c=>c.innerText.trim()));
}

const tbl = document.getElementById('booksTable');
const exportCsv  = document.getElementById('exportCsv');
const exportXlsx = document.getElementById('exportXlsx');
const exportPdf  = document.getElementById('exportPdf');

if (exportCsv && tbl) {
  exportCsv.addEventListener('click', (e)=>{
    e.preventDefault();
    const data = tableTo2DArray(tbl);
    const csv  = data.map(r=>r.map(v=>
      /[",\n]/.test(v) ? '"' + v.replace(/"/g,'""') + '"' : v
    ).join(',')).join('\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'books.csv';
    a.click();
    URL.revokeObjectURL(a.href);
  });
}

if (exportXlsx && tbl) {
  exportXlsx.addEventListener('click', async (e)=>{
    e.preventDefault();
    const wb = XLSX.utils.book_new();
    const data = tableTo2DArray(tbl);
    const ws = XLSX.utils.aoa_to_sheet(data);
    XLSX.utils.book_append_sheet(wb, ws, 'Books');
    XLSX.writeFile(wb, 'books.xlsx');
  });
}

if (exportPdf && tbl) {
  exportPdf.addEventListener('click', async (e)=>{
    e.preventDefault();
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l','pt','a4');
    const headers = [...tbl.tHead.rows[0].cells].map(c=>c.innerText.trim());
    const body = [...tbl.tBodies[0].rows].map(tr => [...tr.cells].slice(0,8).map(td=>td.innerText.trim()));
    doc.text('Books Export', 40, 40);
    doc.autoTable({ head: [headers.slice(0,8)], body, startY: 60, styles: { fontSize: 8 } });
    doc.save('books.pdf');
  });
}

// ====== Make any table row with data-href clickable (but ignore buttons/links) ======
document.addEventListener('click', (e) => {
  const row = e.target.closest('tr[data-href]');
  if (!row) return;
  if (e.target.closest('a, button, input, label, select, textarea')) return;
  window.location.href = row.getAttribute('data-href');
});
