# personal-website

Static personal website at https://www.jalexstark.com, with Little Trips at
https://www.jalexstark.com/games/.

## Build and publish

```sh
npm ci --prefix apps/little-trips
node scripts/build-site.mjs
```

The build copies the existing static pages and files into `site-dist/`, then
builds Little Trips into `site-dist/games/` with `/games/` asset URLs. App source,
development playtest logs, and dependencies are excluded from the published site.
Game source and playtesting instructions live in `apps/little-trips/`.

Netlify builds and publishes `master` using `netlify.toml`. GitHub Actions runs
the game tests, browser checks, and website build on pushes and pull requests.
