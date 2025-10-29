// Print using dedicated print routes with custom layouts
window.printCurrentReport = function(activeTab) {
    try {
        const timePeriod = document.querySelector('select[name="timePeriod"]')?.value || 'all';
        
        const printRoutes = {
            product: `/reports/print/product-movements?timePeriod=${timePeriod}`,
            sales: `/reports/print/sales?timePeriod=${timePeriod}`,
            inventory: `/reports/print/inventory?timePeriod=${timePeriod}`,
            po: `/reports/print/purchase-orders?timePeriod=${timePeriod}`
        };
        
        const printUrl = printRoutes[activeTab];
        if (!printUrl) {
            console.error('No print route found for tab:', activeTab);
            return;
        }

        const printWindow = window.open(printUrl, '_blank');
        if (!printWindow) {
            alert('Popup blocked! Please allow popups for this site to print.');
            return;
        }

    } catch (e) {
        console.error('Print failed', e);
        alert('Print failed: ' + e.message);
    }
};

import './bootstrap';
import './alerts';
import Alpine from 'alpinejs';

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();

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

