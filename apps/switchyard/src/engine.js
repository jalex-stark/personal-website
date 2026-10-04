export const vectors = [[0, -1], [1, 0], [0, 1], [-1, 0]];
export const ports = (type, rotation = 0) => (type === 'cross' ? [0, 1, 2, 3] : type === 'straight' ? [1, 3] : [0, 1]).map(d => (d + rotation) % 4);
export const key = (x, y) => `${x},${y}`;
export function direction(a, b) {
  const i = vectors.findIndex(([x, y]) => b.x - a.x === x && b.y - a.y === y);
  if (i < 0) throw new Error('Stations and tracks must be cardinal neighbors.');
  return i;
}

export function validPlacements(level, placements) {
  if (!placements || typeof placements !== 'object' || Array.isArray(placements)) return false;
  const occupied = new Set(), blocked = new Set(level.obstacles.map(([x, y]) => key(x, y)));
  for (const [id, p] of Object.entries(placements)) {
    if (!level.tiles.some(t => t.id === id) || !p || ![p.x, p.y, p.r].every(Number.isInteger) || p.r < 0 || p.r > 3 || p.x < 0 || p.y < 0 || p.x >= level.size || p.y >= level.size) return false;
    const k = key(p.x, p.y);
    if (blocked.has(k) || occupied.has(k)) return false;
    occupied.add(k);
  }
  return true;
}

// One beat advances each departed train by one cell. Crossings connect opposite
// sides, and trains collide on shared cells or when exchanging cells head-on.
export function simulate(level, placements) {
  if (!validPlacements(level, placements)) throw new Error('Invalid track arrangement.');
  const tracks = new Map(Object.entries(placements).map(([id, p]) => [key(p.x, p.y), { ...p, type: level.tiles.find(t => t.id === id).type }]));
  let trains = level.trains.map(t => ({ id: t.id, x: t.source.x, y: t.source.y, dir: t.direction, state: 'waiting' }));
  const frames = [{ step: 0, trains: trains.map(t => ({ ...t })) }], seen = level.trains.map(() => new Set());
  const inside = p => p.x >= 0 && p.y >= 0 && p.x < level.size && p.y < level.size;
  const equal = (a, b) => a.x === b.x && a.y === b.y;
  for (let step = 1; step <= 100; step++) {
    let failure;
    const next = trains.map((t, i) => {
      const spec = level.trains[i];
      if (t.state === 'arrived' || step <= spec.delay) return { ...t };
      const [dx, dy] = vectors[t.dir], n = { ...t, x: t.x + dx, y: t.y + dy, state: 'moving' };
      const fail = (reason, message) => { failure ||= { reason, message, at: { x: n.x, y: n.y } }; return { ...n, state: 'stopped' }; };
      if (equal(n, spec.target)) return { ...n, state: 'arrived' };
      if (!inside(n)) {
        const other = level.trains.find(s => equal(n, s.target));
        return fail('wrong_exit', other ? `${spec.label} reached ${other.label.toLowerCase()}'s platform.` : `${spec.label} left the yard away from its platform.`);
      }
      const track = tracks.get(key(n.x, n.y));
      if (!track) return fail('missing_track', `${spec.label} ran out of track. Add a connection at the marked square.`);
      const entry = (t.dir + 2) % 4, connections = ports(track.type, track.r);
      if (!connections.includes(entry)) return fail('closed_side', `${spec.label} met the closed side of a track. Try turning that piece.`);
      n.dir = track.type === 'cross' ? t.dir : connections.find(d => d !== entry);
      const visit = `${n.x},${n.y},${n.dir}`;
      if (seen[i].has(visit)) return fail('loop', `${spec.label} is going in circles. Give it a way to its platform.`);
      seen[i].add(visit);
      return n;
    });
    for (let a = 0; a < next.length; a++) for (let b = a + 1; b < next.length; b++) {
      if (next[a].state === 'arrived' || next[b].state === 'arrived' || !inside(next[a]) || !inside(next[b])) continue;
      const headOn = equal(trains[a], next[b]) && equal(trains[b], next[a]);
      if (equal(next[a], next[b]) || headOn) {
        const at = headOn ? { x: (next[a].x + next[b].x) / 2, y: (next[a].y + next[b].y) / 2 } : { x: next[a].x, y: next[a].y };
        failure = { reason: 'collision', at, message: `${level.trains[a].label} and ${level.trains[b].label.toLowerCase()} arrived together. Try a route that gives them more space in time.` };
        next[a] = { ...next[a], ...at, state: 'stopped' }; next[b] = { ...next[b], ...at, state: 'stopped' };
      }
    }
    trains = next;
    frames.push({ step, trains: trains.map(t => ({ ...t })), ...(failure ? { failure } : {}) });
    if (failure) return { ok: false, ...failure, frames };
    if (trains.every(t => t.state === 'arrived')) return { ok: true, frames, steps: step };
  }
  return { ok: false, reason: 'limit', message: 'This route keeps the trains in the yard. Try a shorter connection.', frames };
}
