// Toast alert system - PERFECT SIZING WITH TRANSITIONS
class ToastAlert {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Remove any existing container first
        const existingContainer = document.getElementById('custom-alert-container');
        if (existingContainer) {
            existingContainer.remove();
        }
        
        // Create new container
        this.container = document.createElement('div');
        this.container.id = 'custom-alert-container';
        this.container.className = 'fixed bottom-4 right-4 z-[9999] space-y-3 pointer-events-none';
        
        // Append to body
        document.body.appendChild(this.container);
    }

    show(message, type = 'info', duration = 5000) {
        // Create a DIALOG element instead of div
        const alertDialog = document.createElement('dialog');
        alertDialog.style.cssText = `
            position: fixed;
            top: 8%;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            z-index: 2147483647;
            border: none;
            border-radius: 8px;
            padding: 0;
            margin: 0;
            background: transparent;
            opacity: 0;
            transition: all 0.3s ease-out;
            max-width: 90vw;
        `;

        const colors = {
            success: 'bg-green-100 border border-green-400 text-green-700',
            error: 'bg-red-100 border border-red-400 text-red-700',
            warning: 'bg-yellow-100 border border-yellow-400 text-yellow-700',
            info: 'bg-blue-100 border border-blue-400 text-blue-700'
        };

        alertDialog.innerHTML = `
            <div class="${colors[type]} p-4 rounded-md shadow-lg relative max-w-md">
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        ${this.getAlertIcon(type)}
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium leading-tight break-words">${message}</p>
                    </div>
                    <div class="ml-3 flex-shrink-0">
                        <button class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none transition-colors" 
                                onclick="this.closest('dialog').close()">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(alertDialog);
        
        // Use showModal() to make it appear above other dialogs
        alertDialog.showModal();
        
        // Animate in
        setTimeout(() => {
            alertDialog.style.transform = 'translateX(-50%) translateY(0)';
            alertDialog.style.opacity = '1';
        }, 10);
        
        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                if (alertDialog.parentElement) {
                    alertDialog.style.transform = 'translateX(-50%) translateY(-20px)';
                    alertDialog.style.opacity = '0';
                    setTimeout(() => {
                        alertDialog.close();
                        alertDialog.remove();
                    }, 300);
                }
            }, duration);
        }

        // Close on background click
        alertDialog.addEventListener('click', (e) => {
            if (e.target === alertDialog) {
                alertDialog.style.transform = 'translateX(-50%) translateY(-20px)';
                alertDialog.style.opacity = '0';
                setTimeout(() => {
                    alertDialog.close();
                    alertDialog.remove();
                }, 300);
            }
        });
        
        return alertDialog;
    }

    getAlertIcon(type) {
        const icons = {
            success: `<svg class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                     </svg>`,
            error: `<svg class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>`,
            warning: `<svg class="h-4 w-4 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                     </svg>`,
            info: `<svg class="h-4 w-4 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>`
        };
        return icons[type] || icons.info;
    }

    // Helper methods for common alert types
    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 5000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 5000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
}

// Create global instance
window.Toast = new ToastAlert();