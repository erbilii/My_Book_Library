// Dark mode toggle
const toggleBtn = document.getElementById('themeToggle');
if (toggleBtn) {
  toggleBtn.addEventListener('click', ()=>{
    const cur = document.body.getAttribute('data-theme')||'light';
    const next = cur==='light'?'dark':'light';
    document.body.setAttribute('data-theme', next);
    try{ localStorage.setItem('theme', next);}catch(e){}
  });
}

// Export helpers (CSV, XLSX, PDF) from #booksTable
function tableTo2DArray(table){
  const rows=[...table.querySelectorAll('tr')];
  return rows.map(r=>[...r.children].map(c=>c.innerText.trim()));
}

const tbl = document.getElementById('booksTable');
const exportCsv = document.getElementById('exportCsv');
const exportXlsx = document.getElementById('exportXlsx');
const exportPdf = document.getElementById('exportPdf');

function download(filename, text) {
  const a = document.createElement('a');
  a.setAttribute('href','data:text/plain;charset=utf-8,'+encodeURIComponent(text));
  a.setAttribute('download', filename);
  a.style.display='none';
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
}

if (exportCsv && tbl) {
  exportCsv.addEventListener('click', (e)=>{
    e.preventDefault();
    const data = tableTo2DArray(tbl);
    const csv = data.map(row => row.map(v=>`"${v.replaceAll('"','""')}"`).join(',')).join('\n');
    download('books.csv', csv);
  });
}

if (exportXlsx && tbl) {
  exportXlsx.addEventListener('click', (e)=>{
    e.preventDefault();
    const data = tableTo2DArray(tbl);
    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
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