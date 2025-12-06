# Műhely Kristály - Frontend Implementation Guide

## Overview

A teljes 3D kristály vizualizációs rendszer frontend implementációja Three.js-sel, komplett galériával és profil oldal integrációval.

## 📦 Telepített Komponensek

### Dependencies
- **Three.js v0.170.0** - 3D grafikai engine
- Már meglévő: Vite, Tailwind CSS 4.0, Axios

### Frontend Fájlok

#### JavaScript Komponensek
- `resources/js/components/CrystalViewer.js` - Fő Three.js kristály viewer komponens

#### CSS
- `resources/css/crystal-viewer.css` - Teljes kristály styling
- `resources/css/app.css` - Frissítve importtal

#### Views (Blade Templates)
- `resources/views/layouts/app.blade.php` - Fő layout
- `resources/views/crystals/gallery.blade.php` - Kristály galéria
- `resources/views/crystals/show.blade.php` - Egyedi kristály profil

#### Controllers
- `app/Http/Controllers/CrystalGalleryController.php` - Gallery és show logika

#### Routes
- `routes/web.php` - Frissítve crystal route-okkal

## 🚀 Használat

### 1. NPM Csomagok Telepítése

```bash
docker exec vilagmuhely-php-fpm-1 npm install
```

Ez telepíti a Three.js-t és frissíti a package-lock.json-t.

### 2. Frontend Build

**Development mode:**
```bash
docker exec vilagmuhely-php-fpm-1 npm run dev
```

**Production build:**
```bash
docker exec vilagmuhely-php-fpm-1 npm run build
```

### 3. URL-ek Elérése

- **Kristály Galéria**: http://vilagmuhely.test/crystals/gallery
- **Egyedi Kristály**: http://vilagmuhely.test/crystals/{userId}
- **API Endpoint**: http://vilagmuhely.test/api/v1/crystals/{userId}

## 🎨 CrystalViewer Komponens Használata

### Automatikus Inicializáció

Használd a `data-crystal-viewer` attribútumot bármelyik elemhez:

```html
<div id="my-crystal"
     data-crystal-viewer
     data-user-id="1"
     data-auto-rotate="true"
     data-rotation-speed="0.005"
     data-camera-distance="3"
     data-show-stats="true"
     data-size="large">
</div>
```

### Manuális Inicializáció

```javascript
import { CrystalViewer } from './components/CrystalViewer.js';

const viewer = new CrystalViewer('container-id', userId, {
    autoRotate: true,
    rotationSpeed: 0.005,
    cameraDistance: 3,
    showStats: false,
    size: 'large' // 'small', 'medium', 'large'
});

// Cleanup amikor már nincs rá szükség
viewer.destroy();
```

### Opciók

| Paraméter | Típus | Default | Leírás |
|-----------|-------|---------|--------|
| `autoRotate` | boolean | `true` | Automatikus forgás |
| `rotationSpeed` | number | `0.005` | Forgás sebessége |
| `cameraDistance` | number | `3` | Kamera távolsága |
| `showStats` | boolean | `false` | Statisztikák megjelenítése |
| `size` | string | `'large'` | Méret: small/medium/large |

## 🎭 CSS Osztályok

### Konténer Méretek

```css
.crystal-viewer-container.size-small   /* 200px magasság */
.crystal-viewer-container.size-medium  /* 400px magasság */
.crystal-viewer-container.size-large   /* 600px magasság */
```

### Galéria Layout

```css
.crystal-gallery-grid      /* Rács elrendezés */
.crystal-gallery-item      /* Egy kristály kártya */
.crystal-gallery-viewer    /* Kristály viewer container */
.crystal-gallery-info      /* User info és metrikák */
```

### Profil Oldal

```css
.profile-crystal-section   /* Fő kristály szekció */
.profile-crystal-metrics   /* Metrikák rács */
.profile-metric-card       /* Egy metrika kártya */
```

## 📱 Integráció Példák

### 1. Galéria Oldal (már kész)

URL: `/crystals/gallery`

Features:
- Rács elrendezés 3D kristályokkal
- Sort by: Interaction, Diversity, Engagement
- Auto-rotate minden kristály
- User info és alapmetrikák
- Responsive design

### 2. Profil Oldal (már kész)

URL: `/crystals/{user}`

Features:
- Nagy 3D kristály megjelenítés
- Teljes metrika dashboard
- Stats panel a vieweren
- Crystal magyarázat szekció
- Interaktív (húzható) kristály

### 3. Content List Thumbnail (példa)

Filament ContentResource-ban:

```php
use Filament\Tables;

Tables\Columns\ViewColumn::make('creator.crystal')
    ->view('filament.columns.crystal-thumbnail')
    ->label('Creator Crystal');
```

`resources/views/filament/columns/crystal-thumbnail.blade.php`:

```blade
<div id="crystal-thumb-{{ $getRecord()->creator_id }}"
     class="crystal-thumbnail"
     data-crystal-viewer
     data-user-id="{{ $getRecord()->creator_id }}"
     data-auto-rotate="true"
     data-size="small"
     data-camera-distance="2.5">
</div>
```

### 4. Admin Dashboard Widget (példa)

```php
// app/Filament/Admin/Widgets/MyCrystalWidget.php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class MyCrystalWidget extends Widget
{
    protected static string $view = 'filament.widgets.my-crystal';
    protected int | string | array $columnSpan = 'full';
}
```

`resources/views/filament/widgets/my-crystal.blade.php`:

```blade
<x-filament-widgets::widget>
    <x-filament::section>
        <div id="widget-crystal"
             class="crystal-viewer-container size-medium"
             data-crystal-viewer
             data-user-id="{{ auth()->id() }}"
             data-show-stats="true"
             data-size="medium">
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
```

## 🎨 Testreszabás

### Színek Módosítása

Módosítsd a `crystal-viewer.css`-ben:

```css
.crystal-viewer-container {
    background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);
}
```

### Kamera Beállítások

```javascript
const viewer = new CrystalViewer('my-crystal', userId, {
    cameraDistance: 4,  // Távolabb
    rotationSpeed: 0.01 // Gyorsabb forgás
});
```

### Fények Testreszabása

Módosítsd a `CrystalViewer.js` `setupLights()` metódusát:

```javascript
const mainLight = new THREE.DirectionalLight(0xff0000, 1.0); // Piros fény
mainLight.position.set(10, 10, 10);
```

## 🐛 Hibaelhárítás

### Crystal nem jelenik meg

1. Ellenőrizd a konzolt: `F12` → Console
2. Ellenőrizd az API választ: `/api/v1/crystals/{userId}`
3. Futtasd a migrációkat: `php artisan migrate`
4. Processáld a crystal update-eket: `php artisan crystal:process-updates`

### Canvas üres marad

```javascript
// Ellenőrizd a konténer létezését
const container = document.getElementById('crystal-viewer');
console.log(container); // null esetén nincs elem ezzel az ID-vel
```

### API 404 hiba

```bash
# Clear route cache
docker exec vilagmuhely-php-fpm-1 php artisan route:clear
docker exec vilagmuhely-php-fpm-1 php artisan optimize:clear
```

### Build hibák

```bash
# Tiszta build
docker exec vilagmuhely-php-fpm-1 npm run build

# Ha nem találja a Three.js-t
docker exec vilagmuhely-php-fpm-1 npm install three
```

## ⚡ Performance Optimalizálás

### 1. Kevesebb Facet Kis Nézeteknél

```javascript
// Small thumbnail esetén limit facet count
if (size === 'small' && geometryData.vertices.length > 100) {
    // Használj egyszerűbb geometriát
    return new THREE.IcosahedronGeometry(1, 1);
}
```

### 2. Lazy Loading

```javascript
// Csak akkor inicializálj amikor a viewport-ban van
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            new CrystalViewer(entry.target.id, userId);
            observer.unobserve(entry.target);
        }
    });
});

document.querySelectorAll('[data-crystal-viewer]').forEach(el => {
    observer.observe(el);
});
```

### 3. Lower PixelRatio Mobile-on

```javascript
const pixelRatio = window.innerWidth < 768 ? 1 : Math.min(window.devicePixelRatio, 2);
this.renderer.setPixelRatio(pixelRatio);
```

## 📊 Browser Kompatibilitás

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ⚠️ IE 11 - Nem támogatott (Three.js követelmény)

## 🔮 Következő Lépések

1. **Adatok Generálása**:
   ```bash
   # Hozz létre test user-eket content-tel
   docker exec vilagmuhely-php-fpm-1 php artisan tinker
   >>> $user = User::find(1);
   >>> app(CrystalCalculatorService::class)->recalculateMetrics($user);
   ```

2. **Queue Worker Indítása**:
   ```bash
   docker exec vilagmuhely-php-fpm-1 php artisan queue:listen
   ```

3. **Schedule Worker** (30 perces batch):
   ```bash
   docker exec vilagmuhely-php-fpm-1 php artisan schedule:work
   ```

4. **Látogass el a galériába**:
   http://vilagmuhely.test/crystals/gallery

## 📚 Dokumentáció Linkek

- Three.js Docs: https://threejs.org/docs/
- BufferGeometry: https://threejs.org/docs/#api/en/core/BufferGeometry
- PerspectiveCamera: https://threejs.org/docs/#api/en/cameras/PerspectiveCamera
- MeshPhongMaterial: https://threejs.org/docs/#api/en/materials/MeshPhongMaterial

## 🎉 Kész!

A frontend teljes mértékben implementálva van. Csak telepítsd a package-eket, build-eld az asset-eket, és élvezd a 3D kristályokat! 🔮✨
