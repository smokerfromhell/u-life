# uLife Event Fetching Fix - /api/characters/3/events 500 Error

## Status: ✅ PLAN APPROVED - IMPLEMENTING

### 1. [x] Understand Task & Files ✅
   - Route: api/characters/{id}/events → EventController::getAvailableEvents
   - Issue: EventService::getFilteredAgeSpecificEvents whereNotIn('id', ['ageSpecific_123']) → SQL type error
   - Character 3 likely exists but query fails silently

### 2. [✅] Fix EventService.php shown_event_ids filtering
   - ✅ Add extractShownIdsByType($shownEventIds, $prefix) helper
   - ✅ Fix ALL getFiltered*Events(): daily, cultural, ageSpecific, profession
   - Add logging for debug

### 3. [ ] Test endpoint
   - Refresh Game.vue or `curl http://localhost/api/characters/3/events`
   - Verify events load without 500

### 4. [ ] Verify character_state (FSM migration)
   - Check `php artisan migrate:status | grep fsm_state`
   - Update character 3 if needed

### 5. [ ] Clear caches & complete ✅

**Current Step: Edit EventService.php**

