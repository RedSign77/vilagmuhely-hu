import * as THREE from 'three';

export class WorldViewer {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);

        if (!this.container) {
            console.error(`Container with id "${containerId}" not found`);
            return;
        }

        // Options
        this.options = {
            chunkSize: options.chunkSize ?? 20,
            enableBuilding: options.enableBuilding ?? false,
            showMiniMap: options.showMiniMap ?? true,
            viewMode: options.viewMode ?? 'isometric', // 'isometric' or 'topdown'
            tileWidth: 32,
            tileHeight: 16,
            ...options
        };

        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.structures = new Map(); // Map of structure id -> mesh
        this.currentChunk = { x: -10, y: -10 };
        this.animationId = null;
        this.isLoading = true;
        this.selectedStructure = null;

        // Camera controls
        this.isDragging = false;
        this.previousMousePosition = { x: 0, y: 0 };
        this.cameraPosition = { x: 0, y: 0 };

        this.init();
    }

    async init() {
        try {
            this.showLoading();

            // Setup Three.js scene
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLights();

            // Load initial map chunk
            await this.loadChunk(this.currentChunk.x, this.currentChunk.y);

            // Setup controls
            this.setupControls();

            // Start animation
            this.animate();

            // Handle resize
            window.addEventListener('resize', () => this.onResize());

            this.hideLoading();
        } catch (error) {
            console.error('World viewer initialization error:', error);
            this.showError('Failed to initialize world viewer');
        }
    }

    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x1a1a2e); // Dark blue background

        // Add grid helper
        const gridSize = 100;
        const gridHelper = new THREE.GridHelper(gridSize, gridSize, 0x404040, 0x202020);
        gridHelper.rotation.x = Math.PI / 2;
        this.scene.add(gridHelper);
    }

    setupCamera() {
        // Orthographic camera for isometric view
        const aspect = this.container.clientWidth / this.container.clientHeight;
        const frustumSize = 50;

        this.camera = new THREE.OrthographicCamera(
            frustumSize * aspect / -2,
            frustumSize * aspect / 2,
            frustumSize / 2,
            frustumSize / -2,
            0.1,
            1000
        );

        // Isometric angle
        this.camera.position.set(30, 30, 30);
        this.camera.lookAt(0, 0, 0);
    }

    setupRenderer() {
        this.renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: false,
            powerPreference: 'high-performance'
        });

        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

        // Add renderer to container
        const canvasWrapper = document.createElement('div');
        canvasWrapper.className = 'world-canvas-wrapper';
        canvasWrapper.appendChild(this.renderer.domElement);
        this.container.appendChild(canvasWrapper);
    }

    setupLights() {
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

        // Main directional light
        const mainLight = new THREE.DirectionalLight(0xffffff, 0.6);
        mainLight.position.set(10, 20, 10);
        this.scene.add(mainLight);

        // Fill light
        const fillLight = new THREE.DirectionalLight(0x8888ff, 0.2);
        fillLight.position.set(-10, 5, -10);
        this.scene.add(fillLight);
    }

    async loadChunk(chunkX, chunkY) {
        try {
            const response = await fetch(
                `/api/v1/world/map?chunk_x=${chunkX}&chunk_y=${chunkY}&size=${this.options.chunkSize}`
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                this.renderStructures(result.data.structures);
                this.renderZones(result.data.zones);
            }
        } catch (error) {
            console.error('Error loading chunk:', error);
        }
    }

    renderStructures(structures) {
        // Clear existing structures
        this.structures.forEach((mesh) => {
            this.scene.remove(mesh);
            if (mesh.geometry) mesh.geometry.dispose();
            if (mesh.material) {
                if (Array.isArray(mesh.material)) {
                    mesh.material.forEach(m => m.dispose());
                } else {
                    mesh.material.dispose();
                }
            }
        });
        this.structures.clear();

        // Render new structures
        structures.forEach((structure) => {
            const mesh = this.createStructureMesh(structure);
            this.scene.add(mesh);
            this.structures.set(structure.id, mesh);
        });
    }

    createStructureMesh(structure) {
        const isoPos = this.gridToIso(structure.x, structure.y);

        // Create structure based on type and level
        const height = 0.5 + (structure.level * 0.3);
        const width = 0.8;
        const depth = 0.8;

        const geometry = new THREE.BoxGeometry(width, height, depth);

        // Color based on structure type
        const color = new THREE.Color(structure.color);

        // Apply decay state opacity
        const opacity = structure.decay_state === 'active' ? 1.0 :
                       structure.decay_state === 'fading' ? 0.7 : 0.4;

        const material = new THREE.MeshPhongMaterial({
            color: color,
            transparent: opacity < 1.0,
            opacity: opacity,
            emissive: color,
            emissiveIntensity: 0.2,
            shininess: 60,
        });

        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(isoPos.x, height / 2, isoPos.y);

        // Store structure data
        mesh.userData = {
            structureId: structure.id,
            structureData: structure,
        };

        // Add wireframe for detail
        const wireframeGeo = new THREE.EdgesGeometry(geometry);
        const wireframeMat = new THREE.LineBasicMaterial({
            color: 0xffffff,
            opacity: 0.3,
            transparent: true
        });
        const wireframe = new THREE.LineSegments(wireframeGeo, wireframeMat);
        mesh.add(wireframe);

        return mesh;
    }

    renderZones(zones) {
        // Render zone boundaries (optional visual)
        zones.forEach((zone) => {
            if (!zone.is_unlocked) return;

            const centerX = (zone.bounds.min_x + zone.bounds.max_x) / 2;
            const centerY = (zone.bounds.min_y + zone.bounds.max_y) / 2;
            const width = zone.bounds.max_x - zone.bounds.min_x;
            const height = zone.bounds.max_y - zone.bounds.min_y;

            const isoPos = this.gridToIso(centerX, centerY);

            // Create zone plane
            const geometry = new THREE.PlaneGeometry(width * 0.8, height * 0.8);
            const material = new THREE.MeshBasicMaterial({
                color: new THREE.Color(zone.color),
                transparent: true,
                opacity: 0.05,
                side: THREE.DoubleSide
            });

            const plane = new THREE.Mesh(geometry, material);
            plane.position.set(isoPos.x, 0.01, isoPos.y);
            plane.rotation.x = -Math.PI / 2;
            this.scene.add(plane);
        });
    }

    gridToIso(gridX, gridY) {
        const isoX = (gridX - gridY) * this.options.tileWidth / 2;
        const isoY = (gridX + gridY) * this.options.tileHeight / 2;
        return { x: isoX, y: isoY };
    }

    isoToGrid(screenX, screenY) {
        const gridX = Math.round((screenX / this.options.tileWidth + screenY / this.options.tileHeight));
        const gridY = Math.round((screenY / this.options.tileHeight - screenX / this.options.tileWidth));
        return { x: gridX, y: gridY };
    }

    setupControls() {
        const canvas = this.renderer.domElement;

        // Mouse drag to pan
        canvas.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            this.previousMousePosition = { x: e.clientX, y: e.clientY };
        });

        canvas.addEventListener('mousemove', (e) => {
            if (this.isDragging) {
                const deltaX = e.clientX - this.previousMousePosition.x;
                const deltaY = e.clientY - this.previousMousePosition.y;

                this.camera.position.x -= deltaX * 0.05;
                this.camera.position.z -= deltaY * 0.05;

                this.previousMousePosition = { x: e.clientX, y: e.clientY };
            }
        });

        canvas.addEventListener('mouseup', () => {
            this.isDragging = false;
        });

        canvas.addEventListener('mouseleave', () => {
            this.isDragging = false;
        });

        // Click to select structure
        canvas.addEventListener('click', (e) => {
            if (this.isDragging) return;
            this.handleClick(e);
        });

        // Zoom with mouse wheel
        canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomSpeed = 0.1;
            const delta = e.deltaY > 0 ? 1 : -1;

            this.camera.zoom = Math.max(0.5, Math.min(3, this.camera.zoom - delta * zoomSpeed));
            this.camera.updateProjectionMatrix();
        });

        // Touch support
        canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                this.isDragging = true;
                this.previousMousePosition = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY
                };
            }
        });

        canvas.addEventListener('touchmove', (e) => {
            if (this.isDragging && e.touches.length === 1) {
                const deltaX = e.touches[0].clientX - this.previousMousePosition.x;
                const deltaY = e.touches[0].clientY - this.previousMousePosition.y;

                this.camera.position.x -= deltaX * 0.05;
                this.camera.position.z -= deltaY * 0.05;

                this.previousMousePosition = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY
                };
            }
        });

        canvas.addEventListener('touchend', () => {
            this.isDragging = false;
        });
    }

    handleClick(event) {
        const rect = this.renderer.domElement.getBoundingClientRect();
        const mouse = new THREE.Vector2(
            ((event.clientX - rect.left) / rect.width) * 2 - 1,
            -((event.clientY - rect.top) / rect.height) * 2 + 1
        );

        const raycaster = new THREE.Raycaster();
        raycaster.setFromCamera(mouse, this.camera);

        const intersects = raycaster.intersectObjects(Array.from(this.structures.values()));

        if (intersects.length > 0) {
            const structure = intersects[0].object.userData.structureData;
            this.onStructureSelected(structure);
        }
    }

    onStructureSelected(structure) {
        console.log('Structure selected:', structure);

        // Dispatch custom event for structure selection
        const event = new CustomEvent('structure-selected', {
            detail: structure
        });
        this.container.dispatchEvent(event);

        // Optional: Show structure details panel
        this.showStructureDetails(structure);
    }

    showStructureDetails(structure) {
        // Create or update details panel
        let panel = document.getElementById('structure-details-panel');

        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'structure-details-panel';
            panel.className = 'structure-details-panel';
            this.container.appendChild(panel);
        }

        panel.innerHTML = `
            <div class="structure-details-header">
                <h3>${structure.type_name}</h3>
                <button class="close-btn" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
            <div class="structure-details-body">
                <div class="detail-row">
                    <span class="label">Owner:</span>
                    <span class="value">${structure.user_name}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Level:</span>
                    <span class="value">${structure.level}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Position:</span>
                    <span class="value">(${structure.x}, ${structure.y})</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status:</span>
                    <span class="value">${structure.decay_state}</span>
                </div>
            </div>
        `;
    }

    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        this.renderer.render(this.scene, this.camera);
    }

    onResize() {
        if (!this.camera || !this.renderer) return;

        const aspect = this.container.clientWidth / this.container.clientHeight;
        const frustumSize = 50;

        this.camera.left = frustumSize * aspect / -2;
        this.camera.right = frustumSize * aspect / 2;
        this.camera.top = frustumSize / 2;
        this.camera.bottom = frustumSize / -2;

        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }

    showLoading() {
        this.container.innerHTML = `
            <div class="world-loading">
                <div class="loading-spinner"></div>
                <p>Loading world...</p>
            </div>
        `;
    }

    hideLoading() {
        const loading = this.container.querySelector('.world-loading');
        if (loading) {
            loading.remove();
        }
    }

    showError(message) {
        this.container.innerHTML = `
            <div class="world-error">
                <p>${message}</p>
            </div>
        `;
    }

    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }

        if (this.renderer) {
            this.renderer.dispose();
        }

        if (this.scene) {
            this.scene.traverse((object) => {
                if (object.geometry) {
                    object.geometry.dispose();
                }
                if (object.material) {
                    if (Array.isArray(object.material)) {
                        object.material.forEach(material => material.dispose());
                    } else {
                        object.material.dispose();
                    }
                }
            });
        }

        window.removeEventListener('resize', () => this.onResize());
    }
}

// Auto-initialize world viewers
document.addEventListener('DOMContentLoaded', () => {
    const worldElements = document.querySelectorAll('[data-world-viewer]');

    worldElements.forEach(element => {
        const options = {
            chunkSize: parseInt(element.dataset.chunkSize) || 20,
            enableBuilding: element.dataset.enableBuilding === 'true',
            showMiniMap: element.dataset.showMiniMap !== 'false',
        };

        new WorldViewer(element.id, options);
    });
});
