// Element Detail Modal - Shows element details in a slide-over panel
export class ElementDetailModal {
    constructor() {
        this.isOpen = false;
        this.currentElement = null;
        this.modal = null;
        this.isAuthenticated = false;

        this.init();
    }

    init() {
        // Create modal structure
        this.createModal();

        // Check if user is authenticated
        this.checkAuthentication();
    }

    createModal() {
        // Create modal container
        this.modal = document.createElement('div');
        this.modal.className = 'element-detail-modal';
        this.modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-panel">
                <div class="modal-header">
                    <h2 class="modal-title">Element Details</h2>
                    <button class="modal-close" aria-label="Close">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="modal-content">
                    <!-- Content will be populated here -->
                </div>
            </div>
        `;

        document.body.appendChild(this.modal);

        // Setup event listeners
        this.modal.querySelector('.modal-close').addEventListener('click', () => this.close());
        this.modal.querySelector('.modal-overlay').addEventListener('click', () => this.close());

        // Prevent panel clicks from closing modal
        this.modal.querySelector('.modal-panel').addEventListener('click', (e) => e.stopPropagation());
    }

    checkAuthentication() {
        // Check if user meta tag exists
        const userMeta = document.querySelector('meta[name="user-authenticated"]');
        this.isAuthenticated = userMeta && userMeta.content === 'true';
    }

    async open(element) {
        this.currentElement = element;

        // Fetch full element details
        try {
            const response = await fetch(`/api/v1/world/element/${element.id}`);
            const data = await response.json();

            if (data.success) {
                this.renderContent(data.element);
                this.show();
            } else {
                console.error('Failed to load element details');
            }
        } catch (error) {
            console.error('Error loading element details:', error);
        }
    }

    renderContent(element) {
        const content = this.modal.querySelector('.modal-content');

        content.innerHTML = `
            <div class="element-image">
                <img src="${element.type.image_path || '/images/placeholder.png'}"
                     alt="${element.type.name}"
                     class="element-img">
            </div>

            <div class="element-info">
                <h3 class="element-name">${element.type.name}</h3>

                <div class="element-badges">
                    <span class="badge badge-${element.type.category}">${this.capitalize(element.type.category)}</span>
                    <span class="badge badge-${element.type.rarity}">${this.capitalize(element.type.rarity)}</span>
                </div>

                ${element.type.description ? `
                    <p class="element-description">${element.type.description}</p>
                ` : ''}

                <div class="element-attributes">
                    <div class="attribute">
                        <span class="attribute-label">Position:</span>
                        <span class="attribute-value">(${element.position_x}, ${element.position_y})</span>
                    </div>
                    <div class="attribute">
                        <span class="attribute-label">Biome:</span>
                        <span class="attribute-value">${this.capitalize(element.biome || 'Unknown')}</span>
                    </div>
                    ${element.variant ? `
                        <div class="attribute">
                            <span class="attribute-label">Variant:</span>
                            <span class="attribute-value">${this.capitalize(element.variant)}</span>
                        </div>
                    ` : ''}
                    <div class="attribute">
                        <span class="attribute-label">Interactions:</span>
                        <span class="attribute-value">${element.interaction_count}</span>
                    </div>
                </div>

                ${element.resource_bonus ? this.renderResourceBonus(element) : ''}
            </div>
        `;

        // Setup claim button if present
        const claimButton = content.querySelector('.claim-button');
        if (claimButton) {
            claimButton.addEventListener('click', () => this.claimBonus(element.id));
        }
    }

    renderResourceBonus(element) {
        const bonus = element.resource_bonus;
        const resources = bonus.resources || {};
        const canClaim = bonus.can_claim;

        let html = `
            <div class="resource-bonus">
                <h4 class="bonus-title">Resource Bonus</h4>
                <div class="bonus-resources">
        `;

        for (const [resource, amount] of Object.entries(resources)) {
            const icon = this.getResourceIcon(resource);
            html += `
                <div class="resource-item">
                    <span class="resource-icon">${icon}</span>
                    <span class="resource-name">${this.formatResourceName(resource)}</span>
                    <span class="resource-amount">+${amount}</span>
                </div>
            `;
        }

        html += `</div>`;

        // Add bonus type info
        html += `
            <div class="bonus-info">
                <span class="bonus-type">${bonus.bonus_type === 'repeating' ? 'Repeating' : 'One-time'} Bonus</span>
                ${bonus.bonus_type === 'repeating' ? `
                    <span class="bonus-cooldown">${bonus.cooldown_hours}h cooldown</span>
                ` : ''}
            </div>
        `;

        // Add claim button
        if (this.isAuthenticated) {
            if (canClaim) {
                html += `
                    <button class="claim-button claim-available">
                        Claim Resources
                    </button>
                `;
            } else {
                const nextClaim = bonus.next_claim_at ? new Date(bonus.next_claim_at).toLocaleString() : 'Already claimed';
                html += `
                    <button class="claim-button claim-disabled" disabled>
                        ${bonus.bonus_type === 'repeating' ? `Available at ${nextClaim}` : 'Already Claimed'}
                    </button>
                `;
            }
        } else {
            html += `
                <p class="claim-login-required">
                    <a href="/login">Login</a> to claim resources
                </p>
            `;
        }

        html += `</div>`;

        return html;
    }

    async claimBonus(elementId) {
        const claimButton = this.modal.querySelector('.claim-button');
        if (!claimButton || claimButton.disabled) return;

        // Disable button and show loading
        claimButton.disabled = true;
        claimButton.textContent = 'Claiming...';

        try {
            const response = await fetch(`/api/v1/world/element/${elementId}/interact`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                this.showSuccess(data.message, data.resources_awarded);

                // Update resource bar if exists
                this.updateResourceBar(data.updated_resources);

                // Close modal after short delay
                setTimeout(() => this.close(), 2000);
            } else {
                // Show error
                this.showError(data.message || 'Failed to claim bonus');
                claimButton.disabled = false;
                claimButton.textContent = 'Claim Resources';
            }
        } catch (error) {
            console.error('Error claiming bonus:', error);
            this.showError('An error occurred while claiming resources');
            claimButton.disabled = false;
            claimButton.textContent = 'Claim Resources';
        }
    }

    showSuccess(message, resources) {
        const content = this.modal.querySelector('.modal-content');
        const successDiv = document.createElement('div');
        successDiv.className = 'claim-success';
        successDiv.innerHTML = `
            <div class="success-icon">✓</div>
            <p class="success-message">${message}</p>
            <div class="success-resources">
                ${Object.entries(resources).map(([resource, amount]) => `
                    <span class="success-resource">
                        ${this.getResourceIcon(resource)} +${amount}
                    </span>
                `).join('')}
            </div>
        `;
        content.appendChild(successDiv);
    }

    showError(message) {
        const content = this.modal.querySelector('.modal-content');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'claim-error';
        errorDiv.innerHTML = `
            <p class="error-message">❌ ${message}</p>
        `;
        content.appendChild(errorDiv);

        // Remove after 3 seconds
        setTimeout(() => errorDiv.remove(), 3000);
    }

    updateResourceBar(resources) {
        // Update resource bar elements if they exist
        const resourceElements = {
            stone: document.querySelector('[data-resource="stone"]'),
            wood: document.querySelector('[data-resource="wood"]'),
            crystal_shards: document.querySelector('[data-resource="crystal_shards"]'),
            magic_essence: document.querySelector('[data-resource="magic_essence"]')
        };

        for (const [resource, element] of Object.entries(resourceElements)) {
            if (element && resources[resource] !== undefined) {
                const oldValue = parseInt(element.textContent);
                const newValue = resources[resource];

                element.textContent = newValue;

                // Add animation class
                element.classList.add('resource-updated');
                setTimeout(() => element.classList.remove('resource-updated'), 1000);
            }
        }
    }

    getResourceIcon(resource) {
        const icons = {
            stone: '🪨',
            wood: '🪵',
            crystal_shards: '💎',
            magic_essence: '✨'
        };
        return icons[resource] || '⭐';
    }

    formatResourceName(resource) {
        return resource.split('_').map(word =>
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    show() {
        this.isOpen = true;
        this.modal.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.isOpen = false;
        this.modal.classList.remove('modal-open');
        document.body.style.overflow = '';
        this.currentElement = null;
    }

    destroy() {
        if (this.modal && this.modal.parentNode) {
            this.modal.parentNode.removeChild(this.modal);
        }
    }
}
