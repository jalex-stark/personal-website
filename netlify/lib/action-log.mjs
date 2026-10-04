import { createHash } from 'node:crypto';

const games = new Set(['peel', 'little-trips', 'switchyard']);
const id = /^[a-zA-Z0-9_-]{1,80}$/;
const date = /^\d{4}-\d{2}-\d{2}$/;
const reply = (body, status = 200) => Response.json(body, { status, headers: { 'Cache-Control': 'no-store' } });

// Separate immutable blobs avoid read/modify/write races between tabs and retries.
export function createHandler(openStore, readTokenHash) {
  return async request => {
    if (request.method === 'GET') {
      const token = request.headers.get('Authorization')?.replace(/^Bearer /, '') || '';
      if (createHash('sha256').update(token).digest('hex') !== readTokenHash) return reply({ error: 'Unauthorized' }, 401);
      const url = new URL(request.url), key = url.searchParams.get('key');
      const store = openStore();
      if (key) {
        if (!/^\d{4}-\d{2}-\d{2}\/(peel|little-trips|switchyard)\/[a-zA-Z0-9_-]{1,80}\/[a-zA-Z0-9_-]{1,80}\/[a-f0-9]{64}$/.test(key)) return reply({ error: 'Invalid key' }, 400);
        const batch = await store.get(key, { type: 'json' });
        return batch ? reply(batch) : reply({ error: 'Not found' }, 404);
      }
      const day = url.searchParams.get('date'), game = url.searchParams.get('game');
      if (!day) return reply(await store.list({ directories: true }));
      if (!date.test(day) || (game && !games.has(game))) return reply({ error: 'Invalid filter' }, 400);
      return reply(await store.list({ prefix: `${day}/${game ? `${game}/` : ''}` }));
    }
    if (request.method !== 'POST') return reply({ error: 'Method not allowed' }, 405);
    // Browsers must post from the site. No permissive CORS, IPs, or UA strings in storage.
    const origin = request.headers.get('Origin');
    if (origin && origin !== new URL(request.url).origin) return reply({ error: 'Invalid origin' }, 403);
    if (!request.headers.get('Content-Type')?.startsWith('application/json')) return reply({ error: 'Expected JSON' }, 415);
    if (Number(request.headers.get('Content-Length')) > 65536) return reply({ error: 'Batch too large' }, 413);
    const raw = await request.text();
    if (Buffer.byteLength(raw) > 65536) return reply({ error: 'Batch too large' }, 413);
    let batch;
    try { batch = JSON.parse(raw); } catch { return reply({ error: 'Invalid JSON' }, 400); }
    if (!batch || batch.version !== 1 || !games.has(batch.game) || !id.test(batch.client || '') || !id.test(batch.session || '') || !Array.isArray(batch.events) || !batch.events.length || batch.events.length > 100) return reply({ error: 'Invalid batch' }, 400);
    const seen = new Set();
    for (const event of batch.events) {
      if (!event || typeof event.id !== 'string' || event.id !== `${batch.session}:${event.sequence}` || !Number.isSafeInteger(event.sequence) || event.sequence < 1 || typeof event.action !== 'string' || event.action.length > 100 || !Number.isFinite(Date.parse(event.timestamp)) || seen.has(event.id)) return reply({ error: 'Invalid event' }, 400);
      seen.add(event.id);
    }
    const day = batch.events[0].timestamp.slice(0, 10);
    if (!date.test(day)) return reply({ error: 'Invalid timestamp' }, 400);
    const hash = createHash('sha256').update(raw).digest('hex');
    const key = `${day}/${batch.game}/${batch.client}/${batch.session}/${hash}`;
    // Only acknowledge after durable storage succeeds. Same retry body = same key.
    await openStore().setJSON(key, { ...batch, receivedAt: new Date().toISOString() }, { onlyIfNew: true });
    return reply({ accepted: batch.events.length });
  };
}
