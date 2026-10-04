import { cp, mkdir, readdir, rm, readFile, writeFile } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { join } from 'node:path';

const root = fileURLToPath(new URL('../', import.meta.url));
const output = join(root, 'site-dist');
const infrastructure = new Set([
  'apps', 'scripts', 'site-dist', 'node_modules', 'netlify.toml',
  'README.md', 'package.json', 'package-lock.json', 'netlify', 'tests', 'playtests',
]);

await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
for (const entry of await readdir(root)) {
  if (entry.startsWith('.') || infrastructure.has(entry)) continue;
  await cp(join(root, entry), join(output, entry), { recursive: true });
}

// Include the same journal before app/inline scripts, including Peel work orders.
let build = 'local';
try { build = execFileSync('git', ['rev-parse', '--short', 'HEAD'], { cwd: root, encoding: 'utf8' }).trim(); } catch {}
async function instrument(directory) {
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    const path = join(directory, entry.name);
    if (entry.isDirectory()) await instrument(path);
    else if (entry.name.endsWith('.html')) {
      const html = await readFile(path, 'utf8');
      await writeFile(path, html.replace(/<head(?:\s[^>]*)?>/i, match => `${match}<script src="/games/action-log.js?v=${build}" data-build="${build}"></script>`));
    }
  }
}

for (const [name, slug] of [['little-trips', 'little-trips'], ['letter-tiles', 'peel'], ['switchyard', 'switchyard']]) {
  execFileSync('npm', [
    'run', 'build', '--', `--base=/games/${slug}/`,
    '--outDir', join(output, 'games', slug), '--emptyOutDir',
  ], { cwd: join(root, 'apps', name), stdio: 'inherit' });
}

for (const slug of ['peel', 'little-trips', 'switchyard']) await instrument(join(output, 'games', slug));
