import { defineConfig } from 'vite';
import { appendFile, mkdir } from 'node:fs/promises';
import { join } from 'node:path';

export default defineConfig({ plugins: [{
  name: 'little-trips-local-playtest',
  configureServer(server) {
    let writes = Promise.resolve();
    const automatedSessions = new Set();
    server.middlewares.use('/__little-trips/playtest', async (req, res) => {
      res.setHeader('Content-Type', 'application/json');
      res.setHeader('Cache-Control', 'no-store');
      const reject = (code, error) => { res.statusCode = code; res.end(JSON.stringify({ error })); };
      if (req.method !== 'POST') return reject(405, 'Use POST.');
      if (req.headers['x-little-trips-local'] !== '1' || !req.headers['content-type']?.startsWith('application/json')) return reject(403, 'Local JSON requests only.');
      if (req.headers.origin && req.headers.origin !== `http://${req.headers.host}` && req.headers.origin !== `https://${req.headers.host}`) return reject(403, 'Same-origin requests only.');
      try {
        let body = '';
        for await (const chunk of req) {
          body += chunk;
          if (Buffer.byteLength(body) > 60000) return reject(413, 'Event batch too large.');
        }
        const data = JSON.parse(body);
        if (data.version !== 1 || !Array.isArray(data.events) || !data.events.length || data.events.length > 16) return reject(400, 'Invalid event batch.');
        for (const e of data.events) {
          if (!e || typeof e.event !== 'string' || !/^[a-z_]{1,64}$/.test(e.event) || typeof e.session !== 'string' || e.session.length > 100 || typeof e.timestamp !== 'string') return reject(400, 'Invalid event.');
        }
        // Some browsers omit context-level headers on unload keepalive requests.
        // Remember test sessions so their final lifecycle events stay excluded too.
        if (req.headers['x-little-trips-test'] === '1') {
          for (const e of data.events) automatedSessions.add(e.session);
          res.statusCode = 204; return res.end();
        }
        const humanEvents = data.events.filter(e => !automatedSessions.has(e.session));
        if (!humanEvents.length) { res.statusCode = 204; return res.end(); }
        const directory = join(server.config.root, 'playtests');
        const lines = humanEvents.map(e => JSON.stringify({ ...e, receivedAt: new Date().toISOString() })).join('\n') + '\n';
        const write = writes.catch(() => {}).then(async () => {
          await mkdir(directory, { recursive: true });
          await appendFile(join(directory, 'actions.jsonl'), lines);
        });
        writes = write;
        await write;
        res.end(JSON.stringify({ ok: true }));
      } catch { reject(400, 'Could not save playtest events.'); }
    });
  },
}] });
