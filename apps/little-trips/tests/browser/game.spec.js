import { test, expect } from '@playwright/test';
import { levels } from '../../src/levels.js';
import { solve, rotate, shapeKey, centerOfMass } from '../../src/engine.js';

async function select(page, id) {
  // Keyboard selection uses the default center-of-mass anchor.
  await page.locator(`.item-card[data-piece="${id}"]`).focus();
  await page.keyboard.press('Enter');
}

async function pointer(page, isMobile) {
  if (!isMobile) return {
    down: async p => { await page.mouse.move(p.x, p.y); await page.mouse.down(); },
    move: p => page.mouse.move(p.x, p.y, { steps: 4 }),
    up: () => page.mouse.up(),
    close: async () => {},
  };
  const cdp = await page.context().newCDPSession(page);
  return {
    down: p => cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [p] }),
    move: p => cdp.send('Input.dispatchTouchEvent', { type: 'touchMove', touchPoints: [p] }),
    up: () => cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] }),
    close: () => cdp.detach(),
  };
}

async function rotationFixture(page, crowded = true) {
  const placements = { ...(crowded ? { A: { x: 1, y: 0, rotation: 0 } } : {}), B: { x: 3, y: 0, rotation: 1 } };
  await page.addInitScript(placements => {
    if (sessionStorage.getItem('rotation-fixture-seeded')) return;
    localStorage.setItem('little-trips:save:v1', JSON.stringify({ version: 1, current: 'weekend', trips: { weekend: { placements } } }));
    sessionStorage.setItem('rotation-fixture-seeded', '1');
  }, placements);
  await page.goto('./');
}

async function turnPackedB(page) {
  await page.locator('.packed-piece[data-piece="B"]').focus();
  await page.keyboard.press('Enter');
  await page.locator('.rotate-button').click();
}

async function savedWeekend(page) {
  return page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:save:v1')).trips.weekend);
}

async function packTrip(page, level) {
  const { solution } = solve(level);
  for (const item of level.items) {
    const p = solution[item.id];
    await select(page, item.id);
    let r = item.initialRotation;
    while (shapeKey(rotate(item.cells, r)) !== shapeKey(rotate(item.cells, p.rotation))) {
      await page.locator('.rotate-button').click(); r = (r + 1) % 4;
    }
    const grip = centerOfMass(rotate(item.cells, p.rotation));
    const board = await page.locator('#board').boundingBox(), unit = board.width / level.width;
    await page.locator('#board').click({ position: { x: (p.x + grip.x) * unit, y: (p.y + grip.y) * unit } });
    await expect(page.locator(`.packed-piece[data-piece="${item.id}"]`)).toHaveCount(1);
  }
}

test('all five trips can be packed through the interface, zipped, and remembered', async ({ page }) => {
  test.slow(); // Completing all five trips takes longer on Linux WebKit runners.
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  await page.goto('./');
  for (let i = 0; i < levels.length; i++) {
    await expect(page.locator('#trip-title')).toHaveText(levels[i].title);
    await packTrip(page, levels[i]);
    await expect(page.locator('#zip-button')).toBeVisible();
    await page.locator('#zip-button').click();
    await expect(page.locator('#modal')).toBeVisible();
    if (i < levels.length - 1) await page.getByRole('button', { name: 'The next little trip' }).click();
  }
  await page.getByRole('button', { name: 'Open your travel journal' }).click();
  await expect(page.locator('.trip-status.done')).toHaveCount(5);
  await page.getByRole('button', { name: 'Close travel journal' }).click();
  await page.reload();
  await expect(page.locator('#trip-count')).toHaveText('5/5');
  expect(errors).toEqual([]);
});

test('mouse drag, touch drag, collision rejection, remove, undo, and reload preserve progress', async ({ page, isMobile }) => {
  await page.goto('./');
  const source = await page.locator('.item-card[data-piece="A"] .piece-visual').boundingBox();
  const target = await page.locator('#cell-0-0').boundingBox();
  if (isMobile) {
    // Chromium's input protocol produces real touch/pointer events and capture.
    const from = { x: source.x + source.width / 4, y: source.y + source.height / 4 };
    const to = { x: target.x + target.width / 2, y: target.y + target.height / 2 };
    const cdp = await page.context().newCDPSession(page);
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [from] });
    for (let i = 1; i <= 8; i++) await cdp.send('Input.dispatchTouchEvent', { type: 'touchMove', touchPoints: [{ x: from.x + (to.x - from.x) * i / 8, y: from.y + (to.y - from.y) * i / 8 }] });
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
    await cdp.detach();
  } else {
    await page.mouse.move(source.x + source.width / 4, source.y + source.height / 4);
    await page.mouse.down();
    await page.mouse.move(target.x + target.width / 2, target.y + target.height / 2, { steps: 8 });
    await page.mouse.up();
  }
  await expect(page.locator('.packed-piece[data-piece="A"]')).toHaveCount(1);
  await page.waitForTimeout(450);
  await select(page, 'B');
  await page.locator('#cell-0-0').dispatchEvent('click');
  await expect(page.locator('.packed-piece[data-piece="B"]')).toHaveCount(0);
  await expect(page.locator('#message')).toContainText('Not quite');
  await page.locator('.packed-piece[data-piece="A"]').click();
  await page.getByRole('button', { name: 'Take out' }).click();
  await expect(page.locator('.packed-piece')).toHaveCount(0);
  await page.getByRole('button', { name: 'Undo', exact: true }).click();
  await expect(page.locator('.packed-piece[data-piece="A"]')).toHaveCount(1);
  await page.reload();
  await expect(page.locator('.packed-piece[data-piece="A"]')).toHaveCount(1);
  const saved = await page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:save:v1')));
  expect(saved.trips.weekend.placements.A).toEqual({ x: 0, y: 0, rotation: 0 });
});

test('hints work, a restart can be undone, and playtest events can be exported', async ({ page }) => {
  await page.goto('./');
  await page.getByRole('button', { name: 'A little nudge' }).click();
  await expect(page.locator('.hint-preview .preview-cell')).toHaveCount(4);
  const events = await page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:events:v1')));
  const h = events.findLast(e => e.event === 'hint_used');
  await page.locator(`#cell-${h.target.x}-${h.target.y}`).click({ force: true });
  await expect(page.locator('.packed-piece')).toHaveCount(1);
  await page.getByRole('button', { name: 'Start this trip again' }).click();
  await expect(page.locator('.packed-piece')).toHaveCount(0);
  await page.getByRole('button', { name: 'Undo', exact: true }).click();
  await expect(page.locator('.packed-piece')).toHaveCount(1);
  await page.getByRole('button', { name: 'How to play' }).click();
  const downloadPromise = page.waitForEvent('download');
  await page.getByRole('button', { name: 'Export playtest events' }).click();
  const download = await downloadPromise;
  expect(download.suggestedFilename()).toMatch(/^little-trips-playtest-.*\.json$/);
});

test('keyboard packing and small screen layout work without horizontal overflow', async ({ page, isMobile }) => {
  await page.goto('./');
  await page.locator('.item-card[data-piece="A"]').focus();
  await page.keyboard.press('Enter');
  await page.locator('#board').focus();
  await page.keyboard.press('Enter');
  await expect(page.locator('.packed-piece[data-piece="A"]')).toHaveCount(1);
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
  if (isMobile) {
    await page.getByRole('button', { name: 'Your little trips' }).click();
    await page.locator('[data-action="trip"][data-index="4"]').click();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
  }
});

test('malformed saved data and unavailable storage do not prevent play', async ({ page }) => {
  await page.addInitScript(() => {
    localStorage.setItem('little-trips:save:v1', '{broken');
    localStorage.setItem('little-trips:events:v1', '{}');
  });
  await page.goto('./');
  await expect(page.locator('.item-card')).toHaveCount(4);
  await page.getByRole('button', { name: 'A little nudge' }).click();
  await expect(page.locator('.hint-preview')).toBeVisible();
});

test('off-center grabs follow the pointer without jumping and snap by displacement', async ({ page, isMobile }) => {
  await page.goto('./');
  await select(page, 'A');
  const grid = await page.locator('#board').boundingBox();
  await page.locator('#board').click({ position: { x: grid.width / 4, y: grid.width / 4 } });
  const packed = page.locator('.packed-piece[data-piece="A"]');
  const rect = await packed.boundingBox(), unit = rect.width / 2;
  const from = { x: rect.x + unit * .82, y: rect.y + unit * .2 };
  const input = await pointer(page, isMobile);
  await packed.evaluate(el => { el.dataset.originalNode = 'kept'; });
  await input.down(from);
  await expect(packed).toHaveAttribute('data-original-node', 'kept');
  await input.move({ x: from.x + unit * .35, y: from.y + unit * .15 });
  await expect(page.locator('#drag-ghost')).toBeVisible();
  // WebKit quantizes dispatched mouse coordinates to device pixels.
  await expect.poll(async () => Math.abs((await page.locator('#drag-ghost').boundingBox()).x - (rect.x + unit * .35))).toBeLessThan(1);
  await expect(page.locator('#board-preview')).toHaveClass(/valid/);
  const left = await page.locator('.preview-cell').first().evaluate(el => el.offsetLeft);
  expect(left).toBe(0); // A small motion stays in place even with a grip near the edge.
  await input.move({ x: from.x + unit * 1.1, y: from.y + unit * .15 });
  await input.up();
  await expect(packed).toBeVisible();
  const result = await page.evaluate(() => ({
    saved: JSON.parse(localStorage.getItem('little-trips:save:v1')),
    drag: JSON.parse(localStorage.getItem('little-trips:events:v1')).findLast(e => e.event === 'drag_finished'),
  }));
  expect(result.saved.trips.weekend.placements.A).toEqual({ x: 1, y: 0, rotation: 0 });
  expect(result.drag.outcome).toBe('placed');
  expect(result.drag.before.placements.A.x).toBe(0);
  expect(result.drag.after.placements.A.x).toBe(1);
  expect(result.drag.trace.length).toBeGreaterThan(0);
  expect(result.drag.pointer).toBe(isMobile ? 'touch' : 'mouse');
  const moved = await packed.boundingBox();
  await input.down({ x: moved.x + unit * .2, y: moved.y + unit * .2 });
  await input.move({ x: moved.x + unit * .9, y: moved.y + unit * .2 });
  await expect(page.locator('#drag-ghost')).toBeVisible();
  await page.keyboard.press('Escape'); await input.up();
  await expect(packed).toBeVisible();
  const cancelled = await page.evaluate(() => ({
    save: JSON.parse(localStorage.getItem('little-trips:save:v1')),
    event: JSON.parse(localStorage.getItem('little-trips:events:v1')).findLast(e => e.event === 'drag_cancelled'),
  }));
  expect(cancelled.save.trips.weekend.placements.A.x).toBe(1);
  expect(cancelled.event.reason).toBe('escape');
  await input.close();
});

test('held rotation, blocked drops, blanket returns, and player notes retain useful context', async ({ page, isMobile }) => {
  await page.goto('./');
  const board = await page.locator('#board').boundingBox(), unit = board.width / 4;
  const source = await page.locator('.item-card[data-piece="B"] .piece-visual').boundingBox();
  const from = { x: source.x + source.width * .3, y: source.y + source.height * .1 };
  const input = await pointer(page, isMobile);
  await input.down(from); await input.move({ x: from.x - 10, y: from.y - 10 });
  await expect(page.locator('#drag-ghost')).toBeVisible();
  await page.keyboard.press('r');
  await input.move({ x: board.x + unit * 3.8, y: board.y + unit * .3 });
  await input.up();
  await expect(page.locator('.packed-piece[data-piece="B"]')).toBeVisible();
  const saved = await page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:save:v1')));
  expect(saved.trips.weekend.placements.B).toEqual({ x: 2, y: 0, rotation: 2 });
  await select(page, 'A');
  await page.locator('#board').click({ position: { x: unit, y: unit } });
  const a = await page.locator('.packed-piece[data-piece="A"]').boundingBox();
  await input.down({ x: a.x + unit / 2, y: a.y + unit / 2 });
  await input.move({ x: a.x + unit * 1.5, y: a.y + unit / 2 });
  await expect(page.locator('#board-preview')).toHaveClass(/invalid/);
  await input.up();
  await expect(page.locator('.packed-piece[data-piece="A"]')).toBeVisible();
  const blanket = await page.locator('.blanket-heading').boundingBox();
  await input.down({ x: a.x + unit / 2, y: a.y + unit / 2 });
  await input.move({ x: blanket.x + blanket.width / 2, y: blanket.y + blanket.height / 2 });
  await expect(page.locator('.blanket-panel')).toHaveClass(/drop-ready/);
  await input.up();
  await expect(page.locator('.packed-piece[data-piece="A"]')).toHaveCount(0);
  await expect(page.locator('.item-card[data-piece="A"] .piece-visual')).toBeVisible();
  await input.close();
  await page.getByRole('button', { name: 'How to play' }).click();
  await page.getByLabel('What felt awkward?').fill('The socks were hard to fit.');
  await page.getByRole('button', { name: 'Save a playtest note' }).click();
  await expect(page.locator('#feedback-status')).toContainText('Saved');
  const events = await page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:events:v1')));
  expect(events.find(e => e.event === 'drag_rotated').rotation).toBe(2);
  expect(events.filter(e => e.event === 'drag_finished').map(e => e.outcome)).toEqual(['placed', 'blocked', 'returned']);
  expect(events.findLast(e => e.event === 'placement_rejected').reason).toBe('occupied');
  expect(events.findLast(e => e.event === 'playtest_note').note).toBe('The socks were hard to fit.');
});

test('default grips use shape mass, while click pickups retain their point through placement and rotation', async ({ page, isMobile }) => {
  await page.goto('./');
  const board = await page.locator('#board').boundingBox(), unit = board.width / 4;
  const savedC = () => page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:save:v1')).trips.weekend.placements.C);
  // This point distinguishes the L's center of mass from its bounding-box center.
  await select(page, 'C');
  await page.locator('#board').click({ position: { x: unit * 1.68, y: unit * 1.4 } });
  expect(await savedC()).toEqual({ x: 1, y: 1, rotation: 0 });
  const input = await pointer(page, isMobile);
  for (const rotatePiece of [false, true]) {
    await page.getByRole('button', { name: 'Start this trip again' }).click();
    const visual = await page.locator('.item-card[data-piece="C"] .piece-visual').boundingBox();
    const from = { x: visual.x + visual.width / 8, y: visual.y + visual.height / 8 };
    await input.down(from); await input.up();
    if (rotatePiece) await page.locator('.rotate-button').click();
    await page.locator('#board').click({ position: { x: unit * (rotatePiece ? 2.75 : 1.25), y: unit * 1.25 } });
    expect(await savedC()).toEqual({ x: 1, y: 1, rotation: rotatePiece ? 1 : 0 });
  }
  await page.getByRole('button', { name: 'Start this trip again' }).click();
  const label = await page.locator('.item-card[data-piece="C"] .item-name').boundingBox();
  await input.down({ x: label.x + label.width / 2, y: label.y + label.height / 2 });
  await input.move({ x: board.x + unit * 7 / 6, y: board.y + unit * 11 / 6 });
  await input.up();
  expect(await savedC()).toEqual({ x: 0, y: 1, rotation: 0 });
  const grip = await page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:events:v1')).findLast(e => e.event === 'drag_started').grip);
  expect(grip.kind).toBe('mass'); expect(grip.x).toBeCloseTo(7 / 6, 1); expect(grip.y).toBeCloseTo(5 / 6, 1);
  await input.close();
});

test('packed rotation adjusts at an edge without a take-out step', async ({ page }) => {
  await rotationFixture(page, false);
  await turnPackedB(page);
  await expect(page.locator('.floating-piece')).toHaveCount(0);
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 2, y: 1, rotation: 2 });
  await expect(page.locator('#message')).not.toContainText('tight');
  await page.getByRole('button', { name: 'Undo', exact: true }).click();
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 3, y: 0, rotation: 1 });
});

test('blocked rotations stay in hand and can be dragged or tapped into place as one undoable move', async ({ page, isMobile }) => {
  await rotationFixture(page);
  const grid = await page.locator('#board').boundingBox(), unit = grid.width / 4;
  await turnPackedB(page);
  await expect(page.locator('.floating-piece[data-piece="B"]')).toBeVisible();
  await expect(page.locator('#message')).toContainText('Turn it freely');
  await expect(page.locator('#packed-count')).toHaveText('1 / 4 packed');
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 3, y: 0, rotation: 1 });
  const input = await pointer(page, isMobile);
  const held = await page.locator('.floating-piece').boundingBox();
  await input.down({ x: held.x + unit, y: held.y + unit / 2 });
  await input.move({ x: grid.x + unit * 3, y: grid.y + unit * 2.5 });
  await input.up(); await input.close();
  await expect(page.locator('.floating-piece')).toHaveCount(0);
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 2, y: 2, rotation: 2 });
  await page.getByRole('button', { name: 'Undo', exact: true }).click();
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 3, y: 0, rotation: 1 });
  await turnPackedB(page);
  const position = { x: unit * 2.8, y: unit * 2.5 };
  if (isMobile) await page.locator('#board').tap({ position });
  else await page.locator('#board').click({ position });
  await expect(page.locator('.floating-piece')).toHaveCount(0);
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 2, y: 2, rotation: 2 });
  expect((await savedWeekend(page)).placements.A).toEqual({ x: 1, y: 0, rotation: 0 });
  const events = await page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:events:v1')));
  expect(events.some(e => e.event === 'piece_rotated' && e.lifted)).toBe(true);
  expect(events.findLast(e => e.event === 'piece_placed').fromRotation).toBe(true);
});

test('lifted rotations cancel safely, survive more turns, and can be placed with the keyboard', async ({ page }) => {
  await rotationFixture(page);
  for (const cancel of ['escape', 'undo']) {
    await turnPackedB(page);
    await expect(page.locator('.floating-piece')).toHaveCount(1);
    if (cancel === 'escape') await page.keyboard.press('Escape');
    else {
      // Reloaded boards have no undo history; the in-hand edit can still cancel.
      await expect(page.getByRole('button', { name: 'Undo', exact: true })).toBeEnabled();
      await page.getByRole('button', { name: 'Undo', exact: true }).click();
    }
    await expect(page.locator('.floating-piece')).toHaveCount(0);
    expect((await savedWeekend(page)).placements.B).toEqual({ x: 3, y: 0, rotation: 1 });
  }
  await turnPackedB(page); await page.reload();
  await expect(page.locator('.floating-piece')).toHaveCount(0);
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 3, y: 0, rotation: 1 });
  await turnPackedB(page);
  await page.locator('#board').focus();
  await page.keyboard.press('ArrowDown'); await page.keyboard.press('Enter');
  await expect(page.locator('.floating-piece')).toHaveCount(0);
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 2, y: 2, rotation: 2 });
  await page.getByRole('button', { name: 'Undo', exact: true }).click();
  expect((await savedWeekend(page)).placements.B).toEqual({ x: 3, y: 0, rotation: 1 });
  await turnPackedB(page); await page.keyboard.press('r');
  const events = await page.evaluate(() => JSON.parse(localStorage.getItem('little-trips:events:v1')));
  expect(events.findLast(e => e.event === 'piece_rotated').rotation).toBe(3);
  expect(events.filter(e => e.event === 'rotation_cancelled').map(e => e.reason)).toEqual(['escape', 'undo']);
});
