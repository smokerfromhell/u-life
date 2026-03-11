# TODO: Fix Branching and Continuous Story Flow

## Problem
The game has branching code implemented but the seeder data lacks branching fields (event_category, chain_order, parent_category). Events are all standalone with no narrative connection.

## Solution Plan

### 1. Update AgeSpecificEventSeeder.php
- Add event_category to group related events (education, career, family, health, social, skill)
- Add chain_order to create narrative sequences (1=start, 2=follow-up, etc.)
- Add parent_category to link events to previous events in the chain
- Create continuous story lines:
  - Education chain: elementary → high_school → college → career
  - Family chain: friend → relationship → marriage → parenthood
  - Career chain: job_start → promotion → success/failure
  - Health chain: illness → recovery/complications
  - Social chain: friend → best_friend → romantic → relationship

### 2. Update EventService.php
- Improve getEventsForNarrativePath() to blend chain events with random events
- Add logic to continue incomplete chains before starting new ones
- Make the narrative feel more continuous

### 3. Update Game.vue (if needed)
- Display current narrative path and progress
- Show chain progression in the UI

## Execution Order
1. First: Update AgeSpecificEventSeeder with branching data
2. Second: Update EventService for better continuous flow
3. Third: Test and verify

## Notes
- The existing 300+ events need to be categorized into chains
- Key events should have chain_order=1 as starting points
- Follow-up events should reference parent_category
- Outcome tracking (positive/negative/neutral) affects next events

