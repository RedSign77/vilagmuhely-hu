import * as THREE from 'three';

export class CrystalViewer {
    constructor(containerId, userId, options = {}) {
        this.containerId = containerId;
        this.userId = userId;
        this.container = document.getElementById(containerId);

        if (!this.container) {
            console.error(`Container with id "${containerId}" not found`);
            return;
        }

        // Options
        this.options = {
            autoRotate: options.autoRotate ?? true,
            rotationSpeed: options.rotationSpeed ?? 0.005,
            cameraDistance: options.cameraDistance ?? 3,
            showStats: options.showStats ?? false,
            size: options.size ?? 'large', // 'small', 'medium', 'large'
            ...options
        };

        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.crystal = null;
        this.animationId = null;
        this.isLoading = true;

        this.init();
    }

    async init() {
        try {
            this.showLoading();

            // Fetch crystal data from API
            const data = await this.fetchCrystalData();

            if (!data) {
                this.showError('Failed to load crystal data');
                return;
            }

            // Setup Three.js scene
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLights();

            // Create crystal
            this.createCrystal(data);

            // Setup controls
            this.setupControls();

            // Add stats if enabled
            if (this.options.showStats) {
                this.createStatsPanel(data);
            }

            // Start animation
            this.animate();

            // Handle resize
            window.addEventListener('resize', () => this.onResize());

            this.hideLoading();
        } catch (error) {
            console.error('Crystal viewer initialization error:', error);
            this.showError('Failed to initialize crystal viewer');
        }
    }

    async fetchCrystalData() {
        try {
            const response = await fetch(`/api/v1/crystals/${this.userId}`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            return result.success ? result.data : null;
        } catch (error) {
            console.error('Error fetching crystal data:', error);
            return null;
        }
    }

    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x0a0a0f); // Dark background
    }

    setupCamera() {
        const aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(75, aspect, 0.1, 1000);
        this.camera.position.z = this.options.cameraDistance;
    }

    setupRenderer() {
        this.renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true,
            powerPreference: 'high-performance'
        });

        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

        // Clear loading content and add renderer
        this.container.innerHTML = '';
        this.container.appendChild(this.renderer.domElement);
    }

    setupLights() {
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
        this.scene.add(ambientLight);

        // Main directional light
        const mainLight = new THREE.DirectionalLight(0xffffff, 0.8);
        mainLight.position.set(5, 5, 5);
        this.scene.add(mainLight);

        // Fill light
        const fillLight = new THREE.DirectionalLight(0x4488ff, 0.3);
        fillLight.position.set(-5, 0, -5);
        this.scene.add(fillLight);

        // Rim light
        const rimLight = new THREE.DirectionalLight(0xff8844, 0.3);
        rimLight.position.set(0, -5, -5);
        this.scene.add(rimLight);
    }

    createCrystal(data) {
        const geometry = this.createGeometryFromData(data.geometry);

        // Use dominant color for emissive glow if available
        const dominantColor = data.crystal.colors && data.crystal.colors.length > 0
            ? new THREE.Color(data.crystal.colors[0])
            : new THREE.Color(0x94a3b8);

        const material = new THREE.MeshPhongMaterial({
            vertexColors: true,
            transparent: true,
            opacity: data.crystal.purity,
            emissive: dominantColor,
            emissiveIntensity: data.crystal.glow_intensity * 0.3, // Reduced to not overpower vertex colors
            shininess: 100,
            specular: new THREE.Color(0x888888),
            flatShading: false,
        });

        this.crystal = new THREE.Mesh(geometry, material);
        this.scene.add(this.crystal);

        // Add wireframe overlay for extra detail
        if (this.options.size !== 'small') {
            const wireframeGeo = new THREE.WireframeGeometry(geometry);
            const wireframeMat = new THREE.LineBasicMaterial({
                color: 0xffffff,
                opacity: 0.1,
                transparent: true
            });
            const wireframe = new THREE.LineSegments(wireframeGeo, wireframeMat);
            this.crystal.add(wireframe);
        }
    }

    createGeometryFromData(geometryData) {
        if (!geometryData || !geometryData.vertices || !geometryData.faces) {
            // Fallback: create simple icosahedron
            return new THREE.IcosahedronGeometry(1, 1);
        }

        const geometry = new THREE.BufferGeometry();

        // Convert vertices array to Float32Array
        const vertices = new Float32Array(geometryData.vertices.flat());
        geometry.setAttribute('position', new THREE.BufferAttribute(vertices, 3));

        // Convert colors array to Float32Array
        if (geometryData.colors && geometryData.colors.length > 0) {
            const colors = new Float32Array(geometryData.colors.flat());
            geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
        }

        // Set indices for faces
        if (geometryData.faces && geometryData.faces.length > 0) {
            const indices = new Uint16Array(geometryData.faces.flat());
            geometry.setIndex(new THREE.BufferAttribute(indices, 1));
        }

        // Compute normals for proper lighting
        geometry.computeVertexNormals();

        return geometry;
    }

    setupControls() {
        // Simple mouse/touch interaction for rotation
        let isDragging = false;
        let previousMousePosition = { x: 0, y: 0 };

        this.renderer.domElement.addEventListener('mousedown', (e) => {
            isDragging = true;
            previousMousePosition = { x: e.clientX, y: e.clientY };
        });

        this.renderer.domElement.addEventListener('mousemove', (e) => {
            if (isDragging && this.crystal) {
                const deltaX = e.clientX - previousMousePosition.x;
                const deltaY = e.clientY - previousMousePosition.y;

                this.crystal.rotation.y += deltaX * 0.01;
                this.crystal.rotation.x += deltaY * 0.01;

                previousMousePosition = { x: e.clientX, y: e.clientY };
            }
        });

        this.renderer.domElement.addEventListener('mouseup', () => {
            isDragging = false;
        });

        this.renderer.domElement.addEventListener('mouseleave', () => {
            isDragging = false;
        });

        // Touch support
        this.renderer.domElement.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                isDragging = true;
                previousMousePosition = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY
                };
            }
        });

        this.renderer.domElement.addEventListener('touchmove', (e) => {
            if (isDragging && e.touches.length === 1 && this.crystal) {
                const deltaX = e.touches[0].clientX - previousMousePosition.x;
                const deltaY = e.touches[0].clientY - previousMousePosition.y;

                this.crystal.rotation.y += deltaX * 0.01;
                this.crystal.rotation.x += deltaY * 0.01;

                previousMousePosition = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY
                };
            }
        });

        this.renderer.domElement.addEventListener('touchend', () => {
            isDragging = false;
        });
    }

    createStatsPanel(data) {
        const statsDiv = document.createElement('div');
        statsDiv.className = 'crystal-stats';
        statsDiv.innerHTML = `
            <div class="stat-item">
                <span class="stat-label">Content:</span>
                <span class="stat-value">${data.metrics.total_content}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Diversity:</span>
                <span class="stat-value">${(data.metrics.diversity * 100).toFixed(1)}%</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Facets:</span>
                <span class="stat-value">${data.crystal.facets}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Glow:</span>
                <span class="stat-value">${(data.crystal.glow_intensity * 100).toFixed(0)}%</span>
            </div>
        `;
        this.container.appendChild(statsDiv);
    }

    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());

        // Auto-rotate if enabled
        if (this.options.autoRotate && this.crystal) {
            this.crystal.rotation.y += this.options.rotationSpeed;
        }

        this.renderer.render(this.scene, this.camera);
    }

    onResize() {
        if (!this.camera || !this.renderer) return;

        this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }

    showLoading() {
        this.container.innerHTML = `
            <div class="crystal-loading">
                <div class="loading-spinner"></div>
                <p>Loading crystal...</p>
            </div>
        `;
    }

    hideLoading() {
        const loading = this.container.querySelector('.crystal-loading');
        if (loading) {
            loading.remove();
        }
    }

    showError(message) {
        this.container.innerHTML = `
            <div class="crystal-error">
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
