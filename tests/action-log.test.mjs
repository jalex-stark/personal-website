import test from 'node:test';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { createHandler } from '../netlify/lib/action-log.mjs';

const batch = () => ({ version: 1, game: 'switchyard', client: 'player-1', session: 'session-1', events: [{ id: 'session-1:1', sequence: 1, timestamp: '2026-10-04T12:00:00.000Z', action: 'track_placed', detail: { placements: { straight1: { x: 1, y: 2, r: 0 } } } }] });
const request = (data, headers = {}) => new Request('https://www.jalexstark.com/api/game-actions', { method: 'POST', headers: { 'Content-Type': 'application/json', Origin: 'https://www.jalexstark.com', ...headers }, body: JSON.stringify(data) });
function setup() {
  const values = new Map();
  const store = {
    async setJSON(key, value, options) { if (!options.onlyIfNew || !values.has(key)) values.set(key, structuredClone(value)); },
    async get(key) { return values.get(key) || null; },
    async list({ prefix = '', directories = false }) { return directories ? { directories: [...new Set([...values.keys()].map(key => key.split('/')[0] + '/'))], blobs: [] } : { blobs: [...values.keys()].filter(key => key.startsWith(prefix)).map(key => ({ key })) }; },
  };
  const hash = createHash('sha256').update('private-token').digest('hex');
  return { values, store, handle: createHandler(() => store, hash) };
}

test('durable acknowledgement and idempotent retries preserve complete board actions', async () => {
  const { values, handle } = setup(), data = batch();
  assert.equal((await handle(request(data))).status, 200);
  assert.equal((await handle(request(data))).status, 200);
  assert.equal(values.size, 1);
  assert.deepEqual([...values.values()][0].events, data.events);
  const second = batch(); second.session = 'other-tab'; second.events[0].id = 'other-tab:1';
  await handle(request(second)); assert.equal(values.size, 2);
});
test('storage failure never acknowledges or discards a batch', async () => {
  let attempts = 0;
  const handle = createHandler(() => ({ setJSON: async () => { attempts++; throw Error('Unavailable'); } }), 'unused');
  await assert.rejects(handle(request(batch())), /Unavailable/);
  assert.equal(attempts, 1);
});
test('private retrieval, day/game listing, and cross-origin denial', async () => {
  const { handle, values } = setup(); await handle(request(batch()));
  const get = query => handle(new Request(`https://www.jalexstark.com/api/game-actions${query}`, { headers: { Authorization: 'Bearer private-token' } }));
  assert.equal((await handle(new Request('https://www.jalexstark.com/api/game-actions'))).status, 401);
  assert.equal((await handle(new Request('https://www.jalexstark.com/api/game-actions', { headers: { Authorization: 'Bearer wrong' } }))).status, 401);
  assert.deepEqual((await (await get('')).json()).directories, ['2026-10-04/']);
  assert.equal((await (await get('?date=2026-10-04&game=switchyard')).json()).blobs.length, 1);
  assert.equal((await (await get('?date=2026-10-04&game=peel')).json()).blobs.length, 0);
  assert.deepEqual((await (await get(`?key=${[...values.keys()][0]}`)).json()).events, batch().events);
  assert.equal((await handle(request(batch(), { Origin: 'https://elsewhere.example' }))).status, 403);
  assert.equal((await get('?key=../private')).status, 400);
});
test('reject malformed, duplicate, oversized, and unsupported payloads', async () => {
  const { handle } = setup();
  for (const change of [b => b.game = 'other', b => b.client = '../escape', b => b.events = [], b => b.events[0].sequence = 1.2, b => b.events[0].id = 'wrong', b => b.events[0].timestamp = 'yesterday', b => b.events.push(b.events[0])]) {
    const data = batch(); change(data); assert.equal((await handle(request(data))).status, 400);
  }
  const large = batch(); large.events[0].detail.text = 'x'.repeat(66000);
  assert.equal((await handle(request(large))).status, 413);
  assert.equal((await handle(request(batch(), { 'Content-Type': 'text/plain' }))).status, 415);
  assert.equal((await handle(new Request('https://www.jalexstark.com/api/game-actions', { method: 'DELETE' }))).status, 405);
});
