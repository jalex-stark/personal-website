import test from 'node:test';
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { mkdtemp, readFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import config from '../vite.config.js';

test('local playtest journal archives concurrent events and excludes automated checks', async () => {
  const root = await mkdtemp(join(tmpdir(), 'little-trips-journal-'));
  let handler;
  config.plugins[0].configureServer({ config: { root }, middlewares: { use: (path, fn) => { handler = fn; } } });
  const server = createServer((req, res) => handler(req, res));
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  const url = `http://127.0.0.1:${server.address().port}/__little-trips/playtest`;
  const event = sequence => ({ id: `smoke:${sequence}`, event: 'drag_finished', session: 'smoke', timestamp: new Date().toISOString(), sequence, outcome: 'blocked' });
  const post = (events, extra = {}) => fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Little-Trips-Local': '1', ...extra }, body: JSON.stringify({ version: 1, events }) });
  try {
    const responses = await Promise.all(Array.from({ length: 8 }, (_, i) => post([event(i)])));
    assert.ok(responses.every(r => r.ok));
    assert.equal((await post([{ ...event(999), session: 'automated' }], { 'X-Little-Trips-Test': '1' })).status, 204);
    assert.equal((await post([{ ...event(1001), session: 'automated', event: 'session_end' }])).status, 204);
    assert.equal((await post([event(1000)], { Origin: 'https://unrelated.example' })).status, 403);
    assert.equal((await post([{ event: '../bad' }])).status, 400);
    const events = (await readFile(join(root, 'playtests/actions.jsonl'), 'utf8')).trim().split('\n').map(line => JSON.parse(line));
    assert.equal(events.length, 8);
    assert.deepEqual(events.map(e => e.sequence).sort((a, b) => a - b), [0, 1, 2, 3, 4, 5, 6, 7]);
    assert.ok(events.every(e => e.receivedAt && e.outcome === 'blocked'));
  } finally {
    await new Promise(resolve => server.close(resolve));
    await rm(root, { recursive: true, force: true });
  }
});
