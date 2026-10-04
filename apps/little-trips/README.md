# Little Trips

A playable five-trip prototype of a cozy suitcase-packing game. Built for quick playtesting on phones and desktops before native App Store packaging.

## Run

```sh
npm install
npm run dev
```

Open http://localhost:4178. On a phone on the same Wi-Fi network, use the network URL printed by Vite. The development server binds to the network for that purpose.

## Play

Drag an item into the suitcase, or select it and click/tap where you want its anchor to land. The default anchor is the center of mass of the occupied squares. Picking up an object by clicking its artwork anchors it at that exact point, including for a later click-to-place. Selecting with the keyboard or grabbing the card label uses the center of mass. Rotation carries the anchor around with the piece. Dragging previews the nearest grid position and settles on release. Invalid drops return to their starting spot. Select **Turn** to rotate; **R** also works while dragging. Pieces can rotate but cannot reflect. Fill every square, then choose **Zip up & go**. Packed items can be moved or dragged back anywhere on the blanket. Undo also reverses a restart.

Keyboard: Tab to an item and activate it, then Tab to the suitcase. Arrow keys move the target; Enter places the selected item. R rotates, Delete removes a selected packed item, and Escape clears selection. All five trips are available in the travel journal.

**Turn always works on a packed item.** Rotation keeps the grabbed point and adjusts at suitcase edges when possible. If another item blocks the new orientation, the tile lifts into a movable preview. Drag it, tap a free spot, or use arrows and Enter to place it. Turn again to keep experimenting. Escape or Undo cancels the preview; reload keeps the last valid arrangement. Placing the rotated item is one undoable move.

The hint solver respects existing placements when possible. When the suitcase cannot be completed, it returns the smallest number of blocking items to the blanket and shows a valid placement. Hints never place items automatically.

## Verify

```sh
npm test
npm run build
npx playwright install chromium webkit
npm run test:browser
```

To check a published build, use a trailing slash in its URL:

```sh
PLAYTEST_URL=https://www.jalexstark.com/games/ npm run test:browser
```

Rules tests verify all five exact-cover solutions, connectivity, collisions, rotation, and hints for trapped boards. Browser tests cover complete trips, pointer drag, tap/keyboard controls, undo, persistence, hints, event export, and mobile layout. Mobile dragging uses Chromium's emulated touch input; physical iPhone playtesting is still needed.

## Playtest feedback

Progress and up to 2,500 event records stay in browser local storage. During `npm run dev`, actions also save automatically to **`playtests/actions.jsonl`** on the Mac running Vite. This directory is excluded from Git. The production build makes no telemetry requests. There is no external analytics service or account system. Export a JSON file from **How to play → Export playtest events**.

Movement records include input type, fractional grip, duration, distance, target changes, blocked previews, a bounded board-relative pointer trace, drop outcome, and before/after board states. Pickup, rotation, rejection, cancellation, undo, hint, and menu events supply context. **How to play → What felt awkward?** saves a free-text note with the current board and action sequence. Logs can contain whatever you write in a note.

```sh
npm run report -- /path/to/little-trips-playtest-2026-10-03.json
npm run report -- playtests/actions.jsonl
```

The report summarizes completion, hints, rejections, and restarts, then highlights blocked/outside drops, long holds, common rejection/cancellation locations, and player notes. JSON and JSONL files can be combined; event IDs deduplicate overlapping exports. Browser sessions are not unique people, and these are signals to investigate, not automatic diagnoses. Combine them with watching players and short interviews. Automated browser checks do not enter the on-disk human journal.

## Scope

This is a local browser prototype, with five authored levels, original SVG artwork, sound, reduced-motion support, and local saves. It has no paywall, real purchases, advertisements, native iOS project, or App Store submission. Full Xcode is not installed in the current environment. The next milestone is physical-device playtesting, then an iOS build and StoreKit integration if the core game earns it.

Fonts are bundled locally under their SIL Open Font Licenses in `public/fonts/`: DM Sans and Fraunces, from the Google Fonts project. No external font requests are needed at runtime.
