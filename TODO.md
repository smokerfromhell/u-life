# U:LIFE User Agreement Implementation Plan

## Overview
Add User Agreement/Terms of Service modal to Home.vue, shown on first visit (localStorage tracked). Content covers game usage, data privacy, liability. Styled in galaxy cyberpunk theme.

## Steps
- [ ] 1. Create TODO.md (current)
- [ ] 2. Add User Agreement dialog template to Home.vue
- [ ] 3. Add script logic (ref, localStorage check, accept handler)
- [ ] 4. Add cyberpunk styles for modal
- [ ] 5. Test modal shows once per browser
- [ ] 6. Update TODO.md complete
- [ ] 7. Final verification

## Details
- Modal blocks UI until accepted
- localStorage key: 'ulife_terms_accepted_v1'
- Content: Standard terms adapted for game (13+, fictional sim, data opt-in via existing consent)

