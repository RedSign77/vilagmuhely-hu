<div class="resource-bar">
    <div class="resource-item">
        <div class="resource-icon stone">
            🪨
        </div>
        <div class="resource-info">
            <span class="resource-name">Stone</span>
            <span class="resource-amount">{{ $resources['resources']['stone'] ?? 0 }}</span>
        </div>
    </div>

    <div class="resource-item">
        <div class="resource-icon wood">
            🪵
        </div>
        <div class="resource-info">
            <span class="resource-name">Wood</span>
            <span class="resource-amount">{{ $resources['resources']['wood'] ?? 0 }}</span>
        </div>
    </div>

    <div class="resource-item">
        <div class="resource-icon crystal">
            💎
        </div>
        <div class="resource-info">
            <span class="resource-name">Crystal</span>
            <span class="resource-amount">{{ $resources['resources']['crystal_shards'] ?? 0 }}</span>
        </div>
    </div>

    <div class="resource-item">
        <div class="resource-icon magic">
            ✨
        </div>
        <div class="resource-info">
            <span class="resource-name">Magic</span>
            <span class="resource-amount">{{ $resources['resources']['magic_essence'] ?? 0 }}</span>
        </div>
    </div>

    <div class="resource-item">
        <div class="resource-info">
            <span class="resource-name">Structures Built</span>
            <span class="resource-amount">{{ $resources['resources']['total_structures_built'] ?? 0 }}</span>
        </div>
    </div>
</div>
