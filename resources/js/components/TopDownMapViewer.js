// 2D Canvas-based Top-Down World Map Viewer
export class TopDownMapViewer {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);

        if (!this.container) {
            console.error(`Container with id "${containerId}" not found`);
            return;
        }

        // Options
        this.options = {
            tileSize: options.tileSize ?? 64, // pixels per map unit
            enableInteraction: options.enableInteraction ?? false,
            showGrid: options.showGrid ?? false,
            ...options
        };

        // Canvas and context
        this.canvas = null;
        this.ctx = null;

        // Viewport (camera)
        this.viewport = {
            x: 0,      // camera X position in world coordinates
            y: 0,      // camera Y position in world coordinates
            zoom: 1.0  // zoom level (0.5 = 50%, 2.0 = 200%)
        };

        this.minZoom = 0.3;
        this.maxZoom = 3.0;

        // Data
        this.elements = new Map(); // element instances by ID
        this.imageCache = new Map(); // cached images
        this.mapConfig = null;
        this.isLoading = true;
        this.selectedElement = null;

        // Controls
        this.isDragging = false;
        this.lastMouse = { x: 0, y: 0 };
        this.hoveredElement = null;

        // Animation
        this.animationId = null;

        // Performance
        this.lastViewportBounds = null;

        this.init();
    }

    async init() {
        try {
            this.showLoading();

            // Setup canvas
            this.setupCanvas();

            // Load map configuration
            await this.loadMapConfig();

            // Center camera on origin (0, 0)
            this.centerCamera(0, 0);

            // Load initial viewport
            await this.loadViewportElements();

            // Setup controls
            this.setupControls();

            // Start render loop
            this.startRenderLoop();

            // Handle resize
            window.addEventListener('resize', () => this.onResize());

            this.hideLoading();
        } catch (error) {
            console.error('Map viewer initialization error:', error);
            this.showError('Failed to initialize map viewer');
        }
    }

    setupCanvas() {
        this.canvas = document.createElement('canvas');
        this.canvas.width = this.container.clientWidth;
        this.canvas.height = this.container.clientHeight;
        this.canvas.style.display = 'block';
        this.canvas.style.cursor = 'grab';
        this.ctx = this.canvas.getContext('2d');

        // Clear container and add canvas
        this.container.innerHTML = '';
        this.container.appendChild(this.canvas);
    }

    async loadMapConfig() {
        try {
            const response = await fetch('/api/v1/world/map-config');
            const data = await response.json();

            if (data.success) {
                this.mapConfig = data.config;
                console.log('Map config loaded:', this.mapConfig);
            } else {
                throw new Error('Failed to load map configuration');
            }
        } catch (error) {
            console.error('Error loading map config:', error);
            throw error;
        }
    }

    async loadViewportElements() {
        const bounds = this.getViewportBounds();

        // Check if we need to reload (viewport changed significantly)
        if (this.lastViewportBounds && this.viewportBoundsEqual(bounds, this.lastViewportBounds)) {
            return; // No need to reload
        }

        // Add padding to bounds for smoother loading
        const padding = 20;
        const paddedBounds = {
            minX: Math.floor(bounds.minX - padding),
            maxX: Math.ceil(bounds.maxX + padding),
            minY: Math.floor(bounds.minY - padding),
            maxY: Math.ceil(bounds.maxY + padding)
        };

        try {
            const params = new URLSearchParams(paddedBounds);
            const response = await fetch(`/api/v1/world/map?${params}`);
            const data = await response.json();

            if (data.success) {
                // Clear old elements (outside new viewport)
                this.elements.clear();

                // Load new elements
                for (const element of data.elements) {
                    this.elements.set(element.id, element);

                    // Preload image if not cached
                    if (element.type.image_path && !this.imageCache.has(element.type.image_path)) {
                        await this.loadImage(element.type.image_path);
                    }
                }

                this.lastViewportBounds = bounds;
                console.log(`Loaded ${data.count} elements in viewport`);
            }
        } catch (error) {
            console.error('Error loading elements:', error);
        }
    }

    async loadImage(imagePath) {
        return new Promise((resolve, reject) => {
            if (this.imageCache.has(imagePath)) {
                resolve(this.imageCache.get(imagePath));
                return;
            }

            const img = new Image();
            img.onload = () => {
                this.imageCache.set(imagePath, img);
                resolve(img);
            };
            img.onerror = () => {
                console.warn(`Failed to load image: ${imagePath}`);
                reject(new Error(`Failed to load image: ${imagePath}`));
            };
            img.src = imagePath;
        });
    }

    getViewportBounds() {
        // Calculate visible world coordinates based on camera position and zoom
        const halfWidth = (this.canvas.width / 2) / (this.options.tileSize * this.viewport.zoom);
        const halfHeight = (this.canvas.height / 2) / (this.options.tileSize * this.viewport.zoom);

        return {
            minX: Math.floor(this.viewport.x - halfWidth),
            maxX: Math.ceil(this.viewport.x + halfWidth),
            minY: Math.floor(this.viewport.y - halfHeight),
            maxY: Math.ceil(this.viewport.y + halfHeight)
        };
    }

    viewportBoundsEqual(a, b) {
        if (!a || !b) return false;
        return a.minX === b.minX && a.maxX === b.maxX &&
               a.minY === b.minY && a.maxY === b.maxY;
    }

    // Coordinate conversion: World to Screen
    worldToScreen(worldX, worldY) {
        const screenX = (worldX - this.viewport.x) * this.options.tileSize * this.viewport.zoom + this.canvas.width / 2;
        const screenY = (worldY - this.viewport.y) * this.options.tileSize * this.viewport.zoom + this.canvas.height / 2;
        return { x: screenX, y: screenY };
    }

    // Coordinate conversion: Screen to World
    screenToWorld(screenX, screenY) {
        const worldX = (screenX - this.canvas.width / 2) / (this.options.tileSize * this.viewport.zoom) + this.viewport.x;
        const worldY = (screenY - this.canvas.height / 2) / (this.options.tileSize * this.viewport.zoom) + this.viewport.y;
        return { x: worldX, y: worldY };
    }

    centerCamera(worldX, worldY) {
        this.viewport.x = worldX;
        this.viewport.y = worldY;
    }

    setupControls() {
        // Mouse drag for panning
        this.canvas.addEventListener('mousedown', (e) => this.onMouseDown(e));
        this.canvas.addEventListener('mousemove', (e) => this.onMouseMove(e));
        this.canvas.addEventListener('mouseup', (e) => this.onMouseUp(e));
        this.canvas.addEventListener('mouseleave', (e) => this.onMouseUp(e));

        // Mouse wheel for zooming
        this.canvas.addEventListener('wheel', (e) => this.onWheel(e), { passive: false });

        // Click for selecting elements
        this.canvas.addEventListener('click', (e) => this.onClick(e));

        // Touch support
        this.canvas.addEventListener('touchstart', (e) => this.onTouchStart(e), { passive: false });
        this.canvas.addEventListener('touchmove', (e) => this.onTouchMove(e), { passive: false });
        this.canvas.addEventListener('touchend', (e) => this.onTouchEnd(e));
    }

    onMouseDown(e) {
        this.isDragging = true;
        this.lastMouse = { x: e.clientX, y: e.clientY };
        this.canvas.style.cursor = 'grabbing';
    }

    onMouseMove(e) {
        const mouseX = e.clientX;
        const mouseY = e.clientY;

        if (this.isDragging) {
            // Calculate drag delta
            const dx = mouseX - this.lastMouse.x;
            const dy = mouseY - this.lastMouse.y;

            // Update camera position (inverse movement)
            this.viewport.x -= dx / (this.options.tileSize * this.viewport.zoom);
            this.viewport.y -= dy / (this.options.tileSize * this.viewport.zoom);

            this.lastMouse = { x: mouseX, y: mouseY };
        } else {
            // Check for hover
            const rect = this.canvas.getBoundingClientRect();
            const element = this.getElementAtScreenPos(mouseX - rect.left, mouseY - rect.top);

            if (element !== this.hoveredElement) {
                this.hoveredElement = element;
                this.canvas.style.cursor = element ? 'pointer' : 'grab';
            }
        }
    }

    onMouseUp(e) {
        this.isDragging = false;
        this.canvas.style.cursor = this.hoveredElement ? 'pointer' : 'grab';
    }

    onWheel(e) {
        e.preventDefault();

        // Get mouse position before zoom
        const rect = this.canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;
        const worldPosBefore = this.screenToWorld(mouseX, mouseY);

        // Update zoom
        const zoomFactor = e.deltaY > 0 ? 0.9 : 1.1;
        const newZoom = this.viewport.zoom * zoomFactor;
        this.viewport.zoom = Math.max(this.minZoom, Math.min(this.maxZoom, newZoom));

        // Adjust camera to keep mouse position fixed
        const worldPosAfter = this.screenToWorld(mouseX, mouseY);
        this.viewport.x += worldPosBefore.x - worldPosAfter.x;
        this.viewport.y += worldPosBefore.y - worldPosAfter.y;
    }

    onClick(e) {
        if (!this.options.enableInteraction) return;

        const rect = this.canvas.getBoundingClientRect();
        const element = this.getElementAtScreenPos(e.clientX - rect.left, e.clientY - rect.top);

        if (element) {
            this.selectElement(element);
        } else {
            this.deselectElement();
        }
    }

    getElementAtScreenPos(screenX, screenY) {
        const worldPos = this.screenToWorld(screenX, screenY);

        // Check elements in reverse order (top to bottom)
        const elementsArray = Array.from(this.elements.values());
        for (let i = elementsArray.length - 1; i >= 0; i--) {
            const element = elementsArray[i];
            const distance = Math.sqrt(
                Math.pow(element.position_x - worldPos.x, 2) +
                Math.pow(element.position_y - worldPos.y, 2)
            );

            // Check if click is within element bounds (considering scale)
            const radius = (element.type.max_width / this.options.tileSize) * element.scale / 2;
            if (distance < radius) {
                return element;
            }
        }

        return null;
    }

    selectElement(element) {
        this.selectedElement = element;

        // Dispatch custom event for external listeners
        const event = new CustomEvent('element-selected', {
            detail: { element }
        });
        this.container.dispatchEvent(event);
    }

    deselectElement() {
        this.selectedElement = null;

        const event = new CustomEvent('element-deselected');
        this.container.dispatchEvent(event);
    }

    // Touch events
    onTouchStart(e) {
        e.preventDefault();
        if (e.touches.length === 1) {
            const touch = e.touches[0];
            this.isDragging = true;
            this.lastMouse = { x: touch.clientX, y: touch.clientY };
        }
    }

    onTouchMove(e) {
        e.preventDefault();
        if (e.touches.length === 1 && this.isDragging) {
            const touch = e.touches[0];
            const dx = touch.clientX - this.lastMouse.x;
            const dy = touch.clientY - this.lastMouse.y;

            this.viewport.x -= dx / (this.options.tileSize * this.viewport.zoom);
            this.viewport.y -= dy / (this.options.tileSize * this.viewport.zoom);

            this.lastMouse = { x: touch.clientX, y: touch.clientY };
        }
    }

    onTouchEnd(e) {
        this.isDragging = false;
    }

    onResize() {
        this.canvas.width = this.container.clientWidth;
        this.canvas.height = this.container.clientHeight;
    }

    startRenderLoop() {
        const render = () => {
            this.render();
            this.animationId = requestAnimationFrame(render);
        };
        render();
    }

    stopRenderLoop() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }
    }

    render() {
        // Clear canvas
        this.ctx.fillStyle = '#2d5016'; // Dark grass green background
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

        // Draw grid (optional)
        if (this.options.showGrid) {
            this.drawGrid();
        }

        // Get visible elements and sort by Y position (painter's algorithm)
        const bounds = this.getViewportBounds();
        const visibleElements = Array.from(this.elements.values())
            .filter(el =>
                el.position_x >= bounds.minX && el.position_x <= bounds.maxX &&
                el.position_y >= bounds.minY && el.position_y <= bounds.maxY
            )
            .sort((a, b) => a.position_y - b.position_y); // Back to front

        // Render each element
        for (const element of visibleElements) {
            this.renderElement(element);
        }

        // Highlight selected element
        if (this.selectedElement) {
            this.highlightElement(this.selectedElement);
        }

        // Check if we need to load more elements
        this.checkViewportUpdate();
    }

    drawGrid() {
        const bounds = this.getViewportBounds();
        this.ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
        this.ctx.lineWidth = 1;

        for (let x = Math.floor(bounds.minX); x <= Math.ceil(bounds.maxX); x++) {
            const screenPos = this.worldToScreen(x, 0);
            this.ctx.beginPath();
            this.ctx.moveTo(screenPos.x, 0);
            this.ctx.lineTo(screenPos.x, this.canvas.height);
            this.ctx.stroke();
        }

        for (let y = Math.floor(bounds.minY); y <= Math.ceil(bounds.maxY); y++) {
            const screenPos = this.worldToScreen(0, y);
            this.ctx.beginPath();
            this.ctx.moveTo(0, screenPos.y);
            this.ctx.lineTo(this.canvas.width, screenPos.y);
            this.ctx.stroke();
        }
    }

    renderElement(element) {
        const screenPos = this.worldToScreen(element.position_x, element.position_y);
        const image = this.imageCache.get(element.type.image_path);

        if (!image) {
            // Draw placeholder
            this.ctx.fillStyle = this.getCategoryColor(element.type.category);
            this.ctx.beginPath();
            this.ctx.arc(screenPos.x, screenPos.y, 5 * this.viewport.zoom, 0, Math.PI * 2);
            this.ctx.fill();
            return;
        }

        const width = element.type.max_width * element.scale * this.viewport.zoom;
        const height = element.type.max_height * element.scale * this.viewport.zoom;

        this.ctx.save();
        this.ctx.translate(screenPos.x, screenPos.y);
        this.ctx.rotate((element.rotation * Math.PI) / 180);
        this.ctx.drawImage(image, -width / 2, -height / 2, width, height);
        this.ctx.restore();
    }

    highlightElement(element) {
        const screenPos = this.worldToScreen(element.position_x, element.position_y);
        const radius = (element.type.max_width * element.scale * this.viewport.zoom) / 2 + 5;

        this.ctx.strokeStyle = '#fbbf24'; // Amber
        this.ctx.lineWidth = 3;
        this.ctx.beginPath();
        this.ctx.arc(screenPos.x, screenPos.y, radius, 0, Math.PI * 2);
        this.ctx.stroke();
    }

    getCategoryColor(category) {
        const colors = {
            vegetation: '#10b981',
            water: '#3b82f6',
            terrain: '#f59e0b',
            structure: '#ef4444',
            decoration: '#6b7280'
        };
        return colors[category] || '#6b7280';
    }

    checkViewportUpdate() {
        const currentBounds = this.getViewportBounds();
        if (!this.viewportBoundsEqual(currentBounds, this.lastViewportBounds)) {
            // Viewport changed, load new elements
            this.loadViewportElements();
        }
    }

    showLoading() {
        this.isLoading = true;
        // Could show a loading spinner overlay
    }

    hideLoading() {
        this.isLoading = false;
    }

    showError(message) {
        console.error(message);
        if (this.ctx) {
            this.ctx.fillStyle = '#1f2937';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
            this.ctx.fillStyle = '#ef4444';
            this.ctx.font = '16px sans-serif';
            this.ctx.textAlign = 'center';
            this.ctx.fillText(message, this.canvas.width / 2, this.canvas.height / 2);
        }
    }

    destroy() {
        this.stopRenderLoop();
        if (this.canvas && this.canvas.parentNode) {
            this.canvas.parentNode.removeChild(this.canvas);
        }
    }
}

// Auto-initialize on DOM ready
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const viewerElements = document.querySelectorAll('[data-world-viewer]');
        viewerElements.forEach(element => {
            const options = {
                tileSize: parseInt(element.dataset.tileSize) || 64,
                enableInteraction: element.dataset.enableInteraction === 'true',
                showGrid: element.dataset.showGrid === 'true'
            };
            new TopDownMapViewer(element.id, options);
        });
    });
}
