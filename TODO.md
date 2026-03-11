# Task: Increase Event Count from 3 to 5+ in Game.vue

## Status: ✅ COMPLETED

## Plan
1. Modify EventController.php to fetch 5 random weighted events per category instead of 3
2. Update getDailyEvents() method - ✅ Done
3. Update getCulturalEvents() method - ✅ Done
4. Update getAgeSpecificEventsWithBranching() method - ✅ Done
5. Update getAgeSpecificEvents() method - ✅ Done
6. Update getProfessionEvents() method - ✅ Done
7. Test the changes

## Changes Made:
- In `app/Http/Controllers/EventController.php`:
  - Changed `min(3, $events->count())` to `min(5, $events->count())` in:
    - getDailyEvents() - Now returns 5 events
    - getCulturalEvents() - Now returns 5 events
    - getAgeSpecificEvents() - Now returns 5 events
    - getProfessionEvents() - Now returns 5 events
    - getAgeSpecificEventsWithBranching() - Now returns up to 5 events

