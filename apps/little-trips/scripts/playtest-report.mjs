import { readFileSync } from 'node:fs';

const paths = process.argv.slice(2);
if (!paths.length) {
  console.error('Usage: npm run report -- playtest-export.json [more-exports.json ...]');
  process.exit(1);
}
const events = [];
const seen = new Set();
for (const path of paths) {
  const raw = readFileSync(path, 'utf8');
  const data = path.endsWith('.jsonl') ? { version: 1, events: raw.trim().split('\n').filter(Boolean).map(line => JSON.parse(line)) } : JSON.parse(raw);
  if (data.version !== 1 || !Array.isArray(data.events)) throw new Error(`Unsupported export: ${path}`);
  for (const e of data.events) {
    const key = e.id ?? JSON.stringify(e);
    if (!seen.has(key)) { events.push(e); seen.add(key); }
  }
}
const ids = ['weekend', 'coast', 'camp', 'wedding', 'cat'];
const rows = ids.map(level => {
  const es = events.filter(e => e.level === level);
  const sessions = new Set(es.filter(e => e.event === 'level_start').map(e => e.session));
  const finishes = es.filter(e => e.event === 'level_complete');
  const completedSessions = new Set(finishes.map(e => e.session));
  const times = finishes.map(e => e.activeMs / 1000).sort((a, b) => a - b);
  const middle = Math.floor(times.length / 2);
  const median = times.length ? (times.length % 2 ? times[middle] : (times[middle - 1] + times[middle]) / 2) : null;
  return { level, startedSessions: sessions.size, completedSessions: completedSessions.size,
    completionRate: sessions.size ? `${Math.round(completedSessions.size / sessions.size * 100)}%` : '—',
    medianActiveSeconds: median === null ? '—' : Math.round(median),
    hints: es.filter(e => e.event === 'hint_used').length,
    rejectedPlacements: es.filter(e => e.event === 'placement_rejected').length,
    restarts: es.filter(e => e.event === 'level_restart').length };
});
console.table(rows);
const drags = events.filter(e => e.event === 'drag_finished');
if (drags.length) {
  console.log('Movement signals to investigate:');
  console.table(ids.flatMap(level => {
    const ds = drags.filter(e => e.level === level);
    if (!ds.length) return [];
    const durations = ds.map(e => e.durationMs).sort((a, b) => a - b);
    return [{ level, drags: ds.length,
      placed: ds.filter(e => e.outcome === 'placed').length,
      blocked: ds.filter(e => e.outcome === 'blocked').length,
      outside: ds.filter(e => e.outcome === 'outside').length,
      returned: ds.filter(e => e.outcome === 'returned').length,
      heldOver5Seconds: ds.filter(e => e.durationMs > 5000).length,
      medianDragMs: Math.round((durations[Math.floor((durations.length - 1) / 2)] + durations[Math.floor(durations.length / 2)]) / 2),
    }];
  }));
  const problems = new Map();
  for (const e of events.filter(e => ['placement_rejected', 'rotation_rejected', 'rotation_cancelled', 'drag_cancelled'].includes(e.event))) {
    const key = `${e.level} / ${e.item} / ${e.reason ?? e.event}`;
    problems.set(key, (problems.get(key) ?? 0) + 1);
  }
  if (problems.size) console.table([...problems].sort((a, b) => b[1] - a[1]).slice(0, 10).map(([where, occurrences]) => ({ where, occurrences })));
}
const notes = events.filter(e => e.event === 'playtest_note');
if (notes.length) console.table(notes.map(e => ({ when: e.timestamp, level: e.level, note: e.note })));
console.log(`${events.length} local events from ${new Set(events.map(e => e.session)).size} browser sessions.`);
console.log('Directional playtest evidence only. A session is not a person; exports may be truncated.');
