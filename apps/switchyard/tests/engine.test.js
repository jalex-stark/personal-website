import test from 'node:test';
import assert from 'node:assert/strict';
import { levels } from '../src/levels.js';
import { simulate, validPlacements } from '../src/engine.js';

test('every authored yard has a legal route that delivers every train without a collision', () => {
  for (const level of levels) {
    assert.equal(validPlacements(level, level.solution), true, level.id);
    const result = simulate(level, level.solution);
    assert.equal(result.ok, true, `${level.id}: ${result.message}`);
    assert.ok(result.frames.at(-1).trains.every(t => t.state === 'arrived'));
  }
});
test('missing tracks and closed entry sides identify the square that needs work', () => {
  const l = levels[0];
  const empty = simulate(l, {});
  assert.equal(empty.reason, 'missing_track'); assert.deepEqual(empty.at, { x: 0, y: 2 });
  const turned = structuredClone(l.solution); turned['track-0'].r = 1;
  const result = simulate(l, turned);
  assert.equal(result.reason, 'closed_side'); assert.deepEqual(result.at, { x: 0, y: 2 });
});
test('crossing at the same beat collides; waiting one beat makes both connections safe', () => {
  const l = structuredClone(levels[3]); l.trains[1].delay = 0;
  const collision = simulate(l, l.solution);
  assert.equal(collision.reason, 'collision'); assert.deepEqual(collision.at, { x: 1, y: 1 });
  l.trains[1].delay = 1; assert.equal(simulate(l, l.solution).ok, true);
});
test('head-on exchanges collide between squares instead of letting trains pass through each other', () => {
  const l = structuredClone(levels[0]);
  l.size = 4; l.tiles = l.tiles.slice(0, 4);
  l.trains[0].target = { x: 4, y: 2 };
  l.trains.push({ ...l.trains[0], id: 'other', label: 'Blue', source: { x: 4, y: 2 }, target: { x: -1, y: 2 }, direction: 3 });
  const p = Object.fromEntries(Object.entries(l.solution).slice(0, 4));
  const result = simulate(l, p);
  assert.equal(result.reason, 'collision'); assert.deepEqual(result.at, { x: 1.5, y: 2 });
});
test('overlapping, obstructed, fractional, out-of-bounds and unknown tracks are rejected', () => {
  const l = levels[1], p = structuredClone(l.solution);
  for (const replacement of [{ x: 2, y: 3, r: 0 }, { x: -1, y: 0, r: 0 }, { x: 0.5, y: 0, r: 0 }, { x: 0, y: 0, r: 4 }, { ...p['track-1'] }]) {
    assert.equal(validPlacements(l, { ...p, 'track-0': replacement }), false);
  }
  assert.equal(validPlacements(l, { mystery: { x: 0, y: 0, r: 0 } }), false);
});
