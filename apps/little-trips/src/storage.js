const STATE_KEY = 'little-trips:save:v1';
const EVENT_KEY = 'little-trips:events:v1';

export function readStorage(key, fallback) {
  try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch { return fallback; }
}

export function writeStorage(key, value) {
  try { localStorage.setItem(key, JSON.stringify(value)); return true; } catch { return false; }
}

export function loadSave() { return readStorage(STATE_KEY, {}); }
export function saveGame(value) { return writeStorage(STATE_KEY, value); }

let session;
try {
  session = sessionStorage.getItem('little-trips:session') || crypto.randomUUID();
  sessionStorage.setItem('little-trips:session', session);
} catch { session = `session-${Date.now()}`; }

let events = readStorage(EVENT_KEY, []);
if (!Array.isArray(events)) events = [];
events = events.filter(e => e && typeof e === 'object');
let sequence = Math.max(0, ...events.filter(e => e.session === session && Number.isSafeInteger(e.sequence)).map(e => e.sequence));

export function track(event, properties = {}) {
  const entry = { event, timestamp: new Date().toISOString(), session, sequence: ++sequence, ...properties };
  entry.id = `${session}:${entry.sequence}`;
  events.push(entry);
  events = events.slice(-2500);
  writeStorage(EVENT_KEY, events);
  // The development server archives actions on this Mac. Production builds only
  // retain the existing browser journal; they have no telemetry endpoint.
  if (import.meta.env.DEV) {
    fetch('/__little-trips/playtest', { method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Little-Trips-Local': '1' },
      body: JSON.stringify({ version: 1, events: [entry] }), keepalive: true,
    }).catch(() => {});
  }
}

export function exportEvents() {
  const url = URL.createObjectURL(new Blob([JSON.stringify({ version: 1, exportedAt: new Date().toISOString(), events }, null, 2)], { type: 'application/json' }));
  const a = document.createElement('a');
  a.href = url; a.download = `little-trips-playtest-${new Date().toISOString().slice(0, 10)}.json`;
  a.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
}
