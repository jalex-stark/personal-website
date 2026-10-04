import test from 'node:test';
import assert from 'node:assert/strict';
import { levels } from '../src/levels.js';
import { rotate, dimensions, centerOfMass, shapeKey, absoluteCells, canPlace, isComplete, solve, getHint } from '../src/engine.js';

test('the default grip weighs occupied squares and follows rotations', () => {
  const cells = [[0, 0], [1, 0], [1, 1]];
  const mass = centerOfMass(cells);
  assert.equal(mass.x, 7 / 6);
  assert.equal(mass.y, 5 / 6);
  assert.notDeepEqual(mass, { x: 1, y: 1 });
  for (const level of levels) for (const item of level.items) for (let r = 0; r < 4; r++) {
    const before = rotate(item.cells, r), a = centerOfMass(before), b = centerOfMass(rotate(item.cells, r + 1));
    assert.ok(Math.abs(b.x - (dimensions(before).height - a.y)) < 1e-12);
    assert.ok(Math.abs(b.y - a.x) < 1e-12);
  }
});

test('every authored trip tiles its suitcase with connected pieces and no reflections', () => {
  for (const level of levels) {
    assert.equal(level.items.reduce((n, i) => n + i.cells.length, 0), level.width * level.height);
    for (const item of level.items) {
      const connected = new Set([item.cells[0].join(',')]);
      let changed = true;
      while (changed) {
        changed = false;
        for (const [x, y] of item.cells) {
          if (connected.has(`${x},${y}`)) continue;
          if ([[x-1,y], [x+1,y], [x,y-1], [x,y+1]].some(c => connected.has(c.join(',')))) {
            connected.add(`${x},${y}`); changed = true;
          }
        }
      }
      assert.equal(connected.size, item.cells.length, `${level.id}/${item.id} is disconnected`);
      assert.equal(shapeKey(rotate(item.cells, 4)), shapeKey(item.cells));
    }
    const result = solve(level);
    assert.ok(result.solution, level.id);
    assert.equal(result.exhausted, false);
    assert.ok(isComplete(level, result.solution));
    const covered = Object.entries(result.solution).flatMap(([id, p]) => absoluteCells(level.items.find(i => i.id === id), p)).map(c => c.join(','));
    assert.equal(new Set(covered).size, level.width * level.height);
  }
});

test('illegal packing is rejected: overlap, out of bounds, unknown items, and fractional coordinates', () => {
  const level = levels[0];
  const square = { x: 0, y: 0, rotation: 0 };
  assert.ok(canPlace(level, {}, 'A', square));
  assert.equal(canPlace(level, { A: square }, 'B', square), false);
  assert.equal(canPlace(level, {}, 'A', { x: -1, y: 0, rotation: 0 }), false);
  assert.equal(canPlace(level, {}, 'A', { x: 3, y: 0, rotation: 0 }), false);
  assert.equal(canPlace(level, {}, 'A', { x: .5, y: 0, rotation: 0 }), false);
  assert.equal(canPlace(level, {}, 'A', { x: 0, y: 0, rotation: .5 }), false);
  assert.equal(canPlace(level, {}, 'unknown', square), false);
  assert.equal(isComplete(level, {}), false);
});

test('the solver respects already packed items; hints finish all five trips without disturbing a solvable board', () => {
  for (const level of levels) {
    const placements = {};
    while (!isComplete(level, placements)) {
      const h = getHint(level, placements);
      assert.ok(h, level.id);
      assert.deepEqual(h.release, []);
      assert.ok(canPlace(level, placements, h.itemId, h.placement));
      placements[h.itemId] = h.placement;
    }
    assert.equal(getHint(level, placements), null);
  }
});

test('a trapped arrangement gets the smallest possible reshuffle instead of a misleading hint', () => {
  const level = levels[1];
  let trapped;
  for (const item of level.items) {
    for (let r = 0; r < 4 && !trapped; r++) for (let y = 0; y < level.height && !trapped; y++) for (let x = 0; x < level.width && !trapped; x++) {
      const p = { x, y, rotation: r };
      if (canPlace(level, {}, item.id, p) && !solve(level, { [item.id]: p }).solution) trapped = { [item.id]: p };
    }
  }
  assert.ok(trapped, 'test requires a legal placement that prevents completion');
  const h = getHint(level, trapped);
  assert.deepEqual(h.release, Object.keys(trapped));
  const fixed = Object.fromEntries(Object.entries(trapped).filter(([id]) => !h.release.includes(id)));
  assert.ok(solve(level, { ...fixed, [h.itemId]: h.placement }).solution);
});

test('invalid fixed placements and search exhaustion are distinguished', () => {
  assert.deepEqual(solve(levels[0], { A: { x: -1, y: 0, rotation: 0 } }), { solution: null, nodes: 0, exhausted: false });
  const bounded = solve(levels[4], {}, 0);
  assert.equal(bounded.solution, null);
  assert.equal(bounded.exhausted, true);
});
