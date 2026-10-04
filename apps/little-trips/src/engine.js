// The game and solver share these rules. Pieces may rotate, but never flip.
export function normalize(cells) {
  const minX = Math.min(...cells.map(([x]) => x));
  const minY = Math.min(...cells.map(([, y]) => y));
  return cells.map(([x, y]) => [x - minX, y - minY]).sort((a, b) => a[1] - b[1] || a[0] - b[0]);
}

export function rotate(cells, turns = 1) {
  let result = normalize(cells);
  for (let t = 0; t < ((turns % 4) + 4) % 4; t++) result = normalize(result.map(([x, y]) => [-y, x]));
  return result;
}

export function dimensions(cells) {
  return { width: Math.max(...cells.map(([x]) => x)) + 1, height: Math.max(...cells.map(([, y]) => y)) + 1 };
}

export function centerOfMass(cells) {
  // Each occupied square has equal mass; empty bounding-box corners contribute none.
  return { x: cells.reduce((sum, [x]) => sum + x + .5, 0) / cells.length,
    y: cells.reduce((sum, [, y]) => sum + y + .5, 0) / cells.length };
}

export function shapeKey(cells) { return normalize(cells).map(p => p.join(',')).join(';'); }

export function absoluteCells(item, placement) {
  return rotate(item.cells, placement.rotation).map(([x, y]) => [x + placement.x, y + placement.y]);
}

export function canPlace(level, placements, itemId, placement) {
  const item = level.items.find(p => p.id === itemId);
  if (!item || !Number.isInteger(placement.x) || !Number.isInteger(placement.y) || !Number.isInteger(placement.rotation)) return false;
  const occupied = new Set();
  for (const [id, p] of Object.entries(placements)) {
    if (id === itemId) continue;
    const other = level.items.find(i => i.id === id);
    if (!other) return false;
    for (const [x, y] of absoluteCells(other, p)) occupied.add(`${x},${y}`);
  }
  return absoluteCells(item, placement).every(([x, y]) => x >= 0 && x < level.width && y >= 0 && y < level.height && !occupied.has(`${x},${y}`));
}

export function isComplete(level, placements) {
  if (Object.keys(placements).length !== level.items.length) return false;
  return level.items.every(item => placements[item.id] && canPlace(level, placements, item.id, placements[item.id])) &&
    level.items.reduce((sum, p) => sum + p.cells.length, 0) === level.width * level.height;
}

// Exact-cover search: branch on the empty square with the fewest legal options.
// A bounded search is distinguished from an impossible board in the result.
export function solve(level, fixed = {}, maxNodes = 200000) {
  let occupied = 0n;
  const bit = (x, y) => 1n << BigInt(y * level.width + x);
  const full = (1n << BigInt(level.width * level.height)) - 1n;
  for (const [id, p] of Object.entries(fixed)) {
    if (!canPlace(level, fixed, id, p)) return { solution: null, nodes: 0, exhausted: false };
    for (const [x, y] of absoluteCells(level.items.find(i => i.id === id), p)) occupied |= bit(x, y);
  }
  const options = [];
  for (const item of level.items.filter(i => !fixed[i.id])) {
    const seen = new Set();
    for (let r = 0; r < 4; r++) {
      const cells = rotate(item.cells, r);
      const key = shapeKey(cells);
      if (seen.has(key)) continue;
      seen.add(key);
      const { width, height } = dimensions(cells);
      for (let y = 0; y <= level.height - height; y++) for (let x = 0; x <= level.width - width; x++) {
        let mask = 0n;
        for (const [cx, cy] of cells) mask |= bit(x + cx, y + cy);
        if (!(mask & occupied)) options.push({ id: item.id, x, y, rotation: r, mask });
      }
    }
  }
  let nodes = 0, exhausted = false;
  const remaining = new Set(level.items.filter(i => !fixed[i.id]).map(i => i.id));
  function search(mask, ids, path) {
    if (++nodes > maxNodes) { exhausted = true; return null; }
    if (!ids.size) return mask === full ? path : null;
    let candidates = null;
    for (let index = 0; index < level.width * level.height; index++) {
      const square = 1n << BigInt(index);
      if (mask & square) continue;
      const possible = options.filter(o => ids.has(o.id) && (o.mask & square) && !(o.mask & mask));
      if (!possible.length) return null;
      if (candidates === null || possible.length < candidates.length) candidates = possible;
      if (candidates.length === 1) break;
    }
    for (const o of candidates ?? []) {
      const next = new Set(ids); next.delete(o.id);
      const result = search(mask | o.mask, next, [...path, o]);
      if (result) return result;
      if (exhausted) return null;
    }
    return null;
  }
  const found = search(occupied, remaining, []);
  const solution = found ? { ...fixed, ...Object.fromEntries(found.map(({ id, x, y, rotation }) => [id, { x, y, rotation }])) } : null;
  return { solution, nodes, exhausted };
}

export function getHint(level, placements) {
  if (isComplete(level, placements)) return null;
  const ids = Object.keys(placements);
  for (let count = 0; count <= ids.length; count++) {
    // Few pieces per level, so finding the smallest reshuffle is inexpensive.
    for (let subset = 0; subset < 2 ** ids.length; subset++) {
      const release = ids.filter((_, i) => subset & (1 << i));
      if (release.length !== count) continue;
      const fixed = Object.fromEntries(Object.entries(placements).filter(([id]) => !release.includes(id)));
      const { solution } = solve(level, fixed);
      if (solution) {
        const item = level.items.find(i => !fixed[i.id]);
        return { itemId: item.id, placement: solution[item.id], release };
      }
    }
  }
  return null;
}
