(() => {
    'use strict';

    let deferredInstallPrompt = null;
    const installButton = document.getElementById('installButton');

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker.js').catch(() => {
                // The website remains usable when service worker registration is unavailable.
            });
        });
    }

    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        deferredInstallPrompt = event;
        installButton?.classList.remove('d-none');
    });

    installButton?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) return;

        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        installButton.classList.add('d-none');
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        installButton?.classList.add('d-none');
    });
})();
