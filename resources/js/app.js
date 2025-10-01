// Print only the currently visible report section
window.printCurrentReport = function(activeTab) {
    try {
        const map = {
            product: 'reports-product',
            sales: 'reports-sales', 
            inventory: 'reports-inventory',
            po: 'reports-po'
        };
        const containerId = map[activeTab] || 'reports-product';
        const node = document.getElementById(containerId);
        if (!node) {
            console.error('Printable container not found for tab:', activeTab);
            return;
        }

        const printWindow = window.open('', '_blank');
        if (!printWindow) return;

        // Get report title based on active tab
        const reportTitles = {
            product: 'Product Movements Report',
            sales: 'Sales Report', 
            inventory: 'Inventory Report',
            po: 'Purchase Order Report'
        };
        const reportTitle = reportTitles[activeTab] || 'Report';

        const styles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
            .map(el => el.outerHTML)
            .join('\n');

        const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>${reportTitle}</title>
${styles}
<style>
  @page { 
    size: A4 landscape; 
    margin: 10mm; 
  }
  body { 
    color: #111827; 
    font-family: Arial, sans-serif;
    font-size: 12px;
  }
  .no-print, button, .bg-gray-100, .flex { 
    display: none !important; 
  }
  /* Table styling for print */
  table { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 11px;
  }
  th, td { 
    border: 1px solid #d1d5db; 
    padding: 6px 8px; 
    text-align: left; 
  }
  thead th { 
    background: #4C7B8F !important; 
    color: white !important; 
    font-weight: bold;
  }
  .print-title { 
    font-weight: 700; 
    font-size: 20px; 
    margin-bottom: 8px;
    text-align: center;
  }
  .print-meta { 
    font-size: 11px; 
    color: #6b7280; 
    margin-bottom: 12px;
    text-align: center;
  }
  .print-header {
    border-bottom: 2px solid #4C7B8F;
    padding-bottom: 8px;
    margin-bottom: 12px;
  }
  /* Ensure tables don't break across pages */
  table { page-break-inside: auto; }
  tr { page-break-inside: avoid; page-break-after: auto; }
  thead { display: table-header-group; }
  tfoot { display: table-footer-group; }
  
  /* Fix for any hidden content in reports */
  [x-show], [x-cloak] { display: block !important; }
  
  /* Stats boxes styling */
  .bg-main { background: #4C7B8F !important; color: white !important; }
  .bg-green-100 { background: #f0fdf4 !important; border: 1px solid #bbf7d0; }
  .bg-blue-100 { background: #dbeafe !important; border: 1px solid #93c5fd; }
  .bg-yellow-100 { background: #fef3c7 !important; border: 1px solid #fcd34d; }
  .bg-red-100 { background: #fee2e2 !important; border: 1px solid #fca5a5; }
</style>
</head>
<body>
  <div class="print-header">
    <div class="print-title">${reportTitle}</div>
    <div class="print-meta">Generated: ${new Date().toLocaleString()} | Time Period: ${document.querySelector('select[name="timePeriod"]')?.value || 'All Time'}</div>
  </div>
  <div id="print-root">${node.innerHTML}</div>
  <script>
    window.onload = function(){
      window.focus();
      window.print();
      setTimeout(() => {
        window.close();
      }, 500);
    };
  <\/script>
</body>
</html>`;

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
    } catch (e) {
        console.error('Print failed', e);
        alert('Print failed: ' + e.message);
    }
};

import './bootstrap';
import './alerts';

// Gets ID and inputted password
const showValue = document.getElementById('showValue');
const hideValue = document.getElementById('hideValue');
const passwordValue = document.getElementById('password');

// ONLY ADD EVENT LISTENERS IF ELEMENTS EXIST
if (showValue && hideValue && passwordValue) {
    // Removes the hidden class (built-in with tailwind)
    // and sets the password type="text" or type="password";
    showValue.addEventListener('click', function() {
        showValue.classList.add('hidden');
        hideValue.classList.remove('hidden');
        passwordValue.setAttribute('type', 'text');
    })

    hideValue.addEventListener('click', function() {
        hideValue.classList.add('hidden');
        showValue.classList.remove('hidden');
        passwordValue.setAttribute('type', 'password');
    })
}

