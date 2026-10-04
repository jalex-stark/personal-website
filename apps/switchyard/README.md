# Switchyard

Five small railway puzzles. Place and turn straight tracks, curves and crossings,
then play the timetable to send each engine to its matching platform.

```sh
npm ci
npm run dev
npm test
npm run build
npx playwright install chromium webkit
npm run test:browser
```

The development server runs on port 4183. Published at
https://www.jalexstark.com/games/switchyard/.

Drag a track, or select it and tap a square. Moving onto another track swaps
the pieces. Turn rotates the selection, including packed tracks; R also rotates
during a drag. Drop a packed track on the tray to take it out. Undo reverses
placement, removal, rotation, hints and clearing the yard. Every engine advances
one square per beat after its scheduled departure. Crossings connect opposite
sides; trains collide at shared cells or when exchanging cells head-on. Play
animates each beat and marks the first problem. Stop returns to planning. Hints
place one track from a verified route. There is no planning timer.

Tab and Enter select tracks and place them. R turns, Delete removes, Z undoes,
and Escape clears a selection or stops playback. All yards are available in the
yard picker. Progress saves separately for each yard.

Progress and the last 1,500 semantic action records stay in local storage.
How to play offers notes and a JSON export. Drag events include pointer type,
grip, duration, distance, target changes and the resulting arrangement. Playback
records its first failure or successful arrival. Nothing is sent to a server.

Engine tests verify all authored routes, crossing timing, head-on collisions,
missing tracks, wrong orientations, and invalid arrangements. Browser tests
cover every yard, mouse and emulated touch grips, rotation, failure feedback,
swaps, keyboard placement, undo, saves, notes, export and mobile overflow.

```sh
PLAYTEST_URL=https://www.jalexstark.com/games/switchyard/ npm run test:browser
```

This is a browser prototype; physical phone playtesting remains useful. The
interface uses original SVG rails and trains, with bundled DM Sans and Fraunces
under their included SIL Open Font Licenses.
