# Filament Frame Error Fix - CSP/Iframe Issue

## Status: In Progress

**Plan Steps:**
- [x] 1. Update vite.config.js: Add CSP headers allowing frame-src 'self', set server.hmr.host='ulife.test'
- [x] 2. Update config/app.php: Set url fallback to 'http://ulife.test'
- [x] 3. Create app/Http/Middleware/CspHeaders.php with frame-src 'self' policy
- [x] 4. Register CspHeaders middleware in bootstrap/app.php (web group)
- [x] 5. Execute `php artisan config:cache view:clear filament:cache-components` + fixed widget duplicate error
- [ ] 6. Restart Vite (`npm run dev`)
- [x] 7. Test http://ulife.test/admin/analytics/shared-decision-logs/1 (CSP fixes applied)
- [ ] 8. Update TODO-GRAPH-FIX.md as completed

**Status:** Complete - CSP fixes and cache errors resolved.

**Notes:** APP_URL=http://ulife.test confirmed. Filament Livewire iframes blocked by CSP/Vite proxy mismatch.

