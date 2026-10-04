# personal-website

Static personal website at https://www.jalexstark.com, with a game directory at
https://www.jalexstark.com/games/:

- Peel: `/games/peel/` (the previous `/games/letter-tiles/` URL redirects here)
- Little Trips: `/games/little-trips/`
- Switchyard: `/games/switchyard/`

## Build and publish

```sh
git submodule update --init
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
