import { cp, mkdir, readdir, rm } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { join } from 'node:path';

const root = fileURLToPath(new URL('../', import.meta.url));
const output = join(root, 'site-dist');
const infrastructure = new Set([
  'apps', 'scripts', 'site-dist', 'node_modules', 'netlify.toml',
  'README.md', 'package.json', 'package-lock.json',
]);

await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
for (const entry of await readdir(root)) {
  if (entry.startsWith('.') || infrastructure.has(entry)) continue;
  await cp(join(root, entry), join(output, entry), { recursive: true });
}

const game = join(root, 'apps/little-trips');
execFileSync(process.execPath, [
  join(game, 'node_modules/vite/bin/vite.js'), 'build',
  '--base=/games/', '--outDir', join(output, 'games'), '--emptyOutDir',
], { cwd: game, stdio: 'inherit' });
