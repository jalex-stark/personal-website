# personal-website

Static personal website at https://www.jalexstark.com, with a game directory at
https://www.jalexstark.com/games/:

- Peel: `/games/peel/` (the previous `/games/letter-tiles/` URL redirects here)
- Little Trips: `/games/little-trips/`
- Switchyard: `/games/switchyard/`

## Build and publish

```sh
git submodule update --init
npm ci
npm ci --prefix apps/little-trips
npm ci --prefix apps/letter-tiles
npm ci --prefix apps/switchyard
node scripts/build-site.mjs
```

The build copies the existing static pages and files into `site-dist/`, then
builds each game into `site-dist/games/<game>/` with matching asset URLs. App source,
development playtest logs, and dependencies are excluded from the published site.
Game source and playtesting instructions live in `apps/`. Peel is a
submodule of https://github.com/jalex-stark/peel, pinned to a reviewed commit.
To publish a new Peel version, update that submodule and commit its
new revision in this repository.

Netlify builds and publishes `master` using `netlify.toml`. GitHub Actions runs
the games' tests, browser checks against the production build, and the website
build on pushes and pull requests.

## Production action archive

All three games (including Peel work orders) send actions to `/api/game-actions`.
The shared journal in `games/action-log.js` loads before each game's scripts. It
captures semantic actions with board states, pointer paths while dragging, game
control keys, buttons, puzzle results, lifecycle events, errors, and submitted
notes. Free-text typing is excluded. The visible notice links to
`/games/playtesting.html`. There is no sampling or automatic archive expiration.

IndexedDB retains every unsent action, independently of the games' recent-event
buffers. Batches are acknowledged only after Netlify Blobs stores them. Failed
uploads retry, including on a later visit; retries are deduplicated during export.
Each tab/page gets a new session and sequence, while a random browser ID links
return visits. If browser storage is disabled, the journal falls back to memory.
Abrupt browser closure, clearing storage, blocked requests, or never returning
can still prevent pending actions from reaching the archive.

`netlify/functions/action-logs.mjs` uses the site-wide `game-action-logs-v1` store,
which survives deployments. Writes are immutable batches, so concurrent tabs do
not overwrite one another. Reads require a bearer token. Only its SHA-256 hash
is committed; the token is in `.private/playtest-read-token` on the authoring Mac
(ignored by Git and excluded from the static build). Back up that file securely.
On another machine set `GAME_LOG_READ_TOKEN`; rotate by generating a new token
and updating `netlify/lib/read-token-hash.mjs` before publishing.

```sh
npm run logs                       # today's actions, as JSONL
npm run logs -- --all               # entire archive
npm run logs -- --date 2026-10-04 --game switchyard
```

Downloads live in ignored `playtests/`. Each line includes game, player ID,
session ID, ordered action, timestamp, and detail. Use `--output PATH` to change
the destination. Netlify's Blobs UI can also inspect the underlying batches.
`npm test` checks backend validation, private reads, and retry behavior;
`node scripts/check-action-logs.mjs` checks the built games' collection and outbox
against a simulated server. It does not send automated sessions to production.
