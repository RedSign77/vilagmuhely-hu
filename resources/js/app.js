import './bootstrap';

// Dynamically load CrystalViewer only when needed (code-splitting)
document.addEventListener('DOMContentLoaded', async () => {
    const crystalElements = document.querySelectorAll('[data-crystal-viewer]');

    if (crystalElements.length > 0) {
        // Only import CrystalViewer (and Three.js) if crystal viewer elements exist on the page
        const { CrystalViewer } = await import('./components/CrystalViewer.js');

        crystalElements.forEach(element => {
            const userId = element.dataset.userId;
            const options = {
                autoRotate: element.dataset.autoRotate !== 'false',
                rotationSpeed: parseFloat(element.dataset.rotationSpeed) || 0.005,
                cameraDistance: parseFloat(element.dataset.cameraDistance) || 3,
                showStats: element.dataset.showStats === 'true',
                size: element.dataset.size || 'large',
            };

            new CrystalViewer(element.id, userId, options);
        });
    }
});

// WorldViewer.js is deprecated - use TopDownMapViewer.js as an ES6 module import instead
