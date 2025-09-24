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

        const styles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
            .map(el => el.outerHTML)
            .join('\n');

        const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
${styles}
<style>
  @page { size: A4; margin: 16mm; }
  body { color: #111827; }
  .no-print { display: none !important; }
  /* Tailwind print tweaks */
  table { width: 100%; border-collapse: collapse; }
  th, td { border-bottom: 1px solid #e5e7eb; padding: 6px 8px; text-align: center; }
  thead th { background: #111827; color: white; }
  .print-title { font-weight: 700; font-size: 18px; margin-bottom: 12px; }
  .print-meta { font-size: 12px; color: #6b7280; margin-bottom: 16px; }
</style>
</head>
<body>
  <div class="print-title">${document.title}</div>
  <div class="print-meta">Generated: ${new Date().toLocaleString()}</div>
  <div id="print-root">${node.innerHTML}</div>
  <script>
    window.onload = function(){
      window.focus();
      window.print();
      setTimeout(()=>window.close(), 50);
    };
  <\/script>
</body>
</html>`;

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
    } catch (e) {
        console.error('Print failed', e);
    }
};

import './bootstrap';
import './alerts';

// Gets ID and inputted password
const showValue = document.getElementById('showValue');
const hideValue = document.getElementById('hideValue');
const passwordValue = document.getElementById('password');

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

