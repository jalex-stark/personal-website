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

for (const [name, slug] of [['little-trips', 'little-trips'], ['letter-tiles', 'peel']]) {
  execFileSync('npm', [
    'run', 'build', '--', `--base=/games/${slug}/`,
    '--outDir', join(output, 'games', slug), '--emptyOutDir',
  ], { cwd: join(root, 'apps', name), stdio: 'inherit' });
}
