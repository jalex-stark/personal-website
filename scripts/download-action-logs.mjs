import { readFile, mkdir, writeFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const args = process.argv.slice(2);
const option = name => args.includes(name) ? args[args.indexOf(name) + 1] : undefined;
const token = (process.env.GAME_LOG_READ_TOKEN || await readFile(new URL('../.private/playtest-read-token', import.meta.url), 'utf8')).trim();
const base = option('--url') || 'https://www.jalexstark.com/api/game-actions';
const game = option('--game');
const get = async params => {
  const url = new URL(base);
  for (const [key, value] of Object.entries(params)) if (value) url.searchParams.set(key, value);
  for (let attempt = 0; attempt < 7; attempt++) {
    const response = await fetch(url, { headers: { Authorization: `Bearer ${token}` } });
    if (response.ok) return response.json();
    if ((response.status === 429 || response.status >= 500) && attempt < 6) {
      await new Promise(resolve => setTimeout(resolve, Math.min(30000, 1000 * 2 ** attempt)));
      continue;
    }
    throw Error(`Archive returned ${response.status}`);
  }
};
let days = [option('--date') || new Date().toISOString().slice(0, 10)];
if (args.includes('--all')) days = (await get({})).directories.map(day => day.replace(/\/$/, ''));
const rows = new Map();
let batches = 0;
for (const date of days) {
  const { blobs } = await get({ date, game });
  for (let i = 0; i < blobs.length; i += 8) {
    const group = await Promise.all(blobs.slice(i, i + 8).map(({ key }) => get({ key })));
    for (const batch of group) {
      batches++;
      for (const event of batch.events) {
        const id = `${batch.game}:${batch.client}:${event.id}`;
        if (!rows.has(id)) rows.set(id, { version: 1, game: batch.game, client: batch.client, session: batch.session, receivedAt: batch.receivedAt, ...event });
      }
    }
  }
}
const events = [...rows.values()].sort((a, b) => a.timestamp.localeCompare(b.timestamp) || a.sequence - b.sequence);
const output = resolve(option('--output') || `playtests/${game || 'all'}-${days.length === 1 ? days[0] : 'all'}.jsonl`);
await mkdir(resolve(output, '..'), { recursive: true });
await writeFile(output, events.map(event => JSON.stringify(event)).join('\n') + (events.length ? '\n' : ''));
const sessions = new Set(events.map(e => e.session));
console.log(`Downloaded ${events.length} actions from ${sessions.size} sessions (${batches} batches) to ${output}`);
