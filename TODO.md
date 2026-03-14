# Fix Memory Logs Fetch Error in Game.vue

## Step 1: Update Character Model (add anon_character_id support) ✅
- [x] Edit app/Models/Character.php (add fillable + accessor)

## Step 2: Fix CharacterController decisionLogs query ✅
- [x] Edit app/Http/Controllers/CharacterController.php (map data JSON fields)

## Step 3: Fix EventController logging (set anon_character_id, top-level fields) ✅
- [x] Edit app/Http/Controllers/EventController.php (ensure logging populates correctly)

## Step 4: Improve Game.vue error handling & UX ✅
- [x] Edit resources/js/pages/Game.vue (better error handling)

## Step 5: Database Migration Check & Run ✅
- [x] anon_character_id accessor added (no migration needed - computed)
- [x] Table shared_decision_logs exists

## Step 6: Testing
- [ ] Enable share_consent in profile
- [ ] Make decisions → toggle Memory Log → verify data loads
- [ ] Test empty state & errors

## Step 7: Cleanup
- [ ] `php artisan cache:clear && php artisan config:clear`
- [ ] Frontend: `npm run dev`

