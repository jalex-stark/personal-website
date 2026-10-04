import { test, expect } from '@playwright/test';
import { levels } from '../../src/levels.js';

const state = page => page.evaluate(() => JSON.parse(localStorage.getItem('switchyard:save:v1')));
async function pointer(page, mobile) {
  if (!mobile) return { down: async p => { await page.mouse.move(p.x, p.y); await page.mouse.down(); }, move: p => page.mouse.move(p.x, p.y, { steps: 5 }), up: () => page.mouse.up(), close: async () => {} };
  const cdp = await page.context().newCDPSession(page);
  return { down: p => cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [p] }), move: p => cdp.send('Input.dispatchTouchEvent', { type: 'touchMove', touchPoints: [p] }), up: () => cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] }), close: () => cdp.detach() };
}

test('every yard can be completed through the editor and playback, with progress saved', async ({ page }) => {
  test.slow();
  const errors = []; page.on('pageerror', e => errors.push(e.message));
  await page.goto('./');
  await expect(page.locator('#fail-marker')).toBeHidden();
  await page.getByRole('button', { name: 'Playback speed 1 times' }).click();
  for (let i = 0; i < levels.length; i++) {
    await expect(page.locator('#yard-title')).toHaveText(levels[i].title);
    for (const _ of levels[i].tiles) await page.getByRole('button', { name: 'Show one connection' }).click();
    expect((await state(page)).yards[levels[i].id].placements).toEqual(levels[i].solution);
    await page.getByRole('button', { name: 'Play the plan' }).click();
    await expect(page.locator('#message')).toHaveText('Everyone made their connection.', { timeout: 15000 });
    if (i < levels.length - 1) await page.getByRole('button', { name: 'Next yard' }).click();
  }
  await expect(page.locator('.progress')).toHaveText('5 / 5 connections made');
  await page.reload(); await expect(page.locator('.progress')).toHaveText('5 / 5 connections made');
  expect(errors).toEqual([]);
});

test('exact grips follow mouse and touch; tracks rotate, undo and survive reload', async ({ page, isMobile }) => {
  await page.goto('./');
  const source = await page.locator('.rack-track[data-tile="track-0"]').boundingBox();
  const target = await page.locator('[data-cell="0,2"]').boundingBox();
  const unit = (await page.locator('#cells').boundingBox()).width / 5;
  const from = { x: source.x + source.width * .72, y: source.y + source.height * .27 };
  const to = { x: target.x + target.width / 2, y: target.y + target.height / 2 };
  const input = await pointer(page, isMobile);
  await input.down(from); await input.move(to);
  await expect(page.locator('#drag-ghost')).toBeVisible();
  await expect.poll(async () => Math.abs((await page.locator('#drag-ghost').boundingBox()).x - (to.x - .72 * unit))).toBeLessThan(1);
  await input.up(); await input.close();
  await expect(page.locator('[data-cell="0,2"]')).toHaveAttribute('data-tile', 'track-0');
  await page.getByRole('button', { name: 'Turn', exact: false }).click();
  expect((await state(page)).yards[levels[0].id].placements['track-0'].r).toBe(1);
  await page.getByRole('button', { name: 'Playback speed 1 times' }).click();
  await page.getByRole('button', { name: 'Play the plan' }).click();
  await expect(page.locator('#message')).toContainText('closed side');
  await expect(page.locator('#fail-marker')).not.toHaveAttribute('hidden');
  await page.getByRole('button', { name: 'Undo', exact: false }).click();
  expect((await state(page)).yards[levels[0].id].placements['track-0'].r).toBe(0);
  await page.reload();
  await expect(page.locator('[data-cell="0,2"]')).toHaveAttribute('data-tile', 'track-0');
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
});

test('keyboard placement, swapping, clear undo, player notes and action export work', async ({ page }) => {
  await page.goto('./');
  for (const [id, x] of [['track-0', 0], ['track-1', 1]]) {
    await page.locator(`.rack-track[data-tile="${id}"]`).focus(); await page.keyboard.press('Enter');
    await page.locator(`[data-cell="${x},2"]`).focus(); await page.keyboard.press('Enter');
  }
  await page.locator('[data-cell="0,2"]').focus(); await page.keyboard.press('Enter');
  // Picking up a different track after a placement must not move the last one.
  await expect(page.locator('[data-cell="0,2"]')).toHaveAttribute('data-tile', 'track-0');
  await expect(page.locator('[data-cell="1,2"]')).toHaveAttribute('data-tile', 'track-1');
  await page.locator('[data-cell="1,2"]').focus(); await page.keyboard.press('Enter');
  await expect(page.locator('[data-cell="1,2"]')).toHaveAttribute('data-tile', 'track-0');
  await expect(page.locator('[data-cell="0,2"]')).toHaveAttribute('data-tile', 'track-1');
  await page.locator('[data-cell="1,2"]').focus(); await page.keyboard.press('Enter');
  await page.locator('[data-cell="2,2"]').focus(); await page.keyboard.press('Enter');
  await expect(page.locator('[data-cell="2,2"]')).toHaveAttribute('data-tile', 'track-0');
  await page.getByRole('button', { name: 'Clear this yard' }).click();
  await expect(page.locator('.cell[data-tile]')).toHaveCount(0);
  await page.getByRole('button', { name: 'Undo', exact: false }).click();
  await expect(page.locator('.cell[data-tile]')).toHaveCount(2);
  await page.getByRole('button', { name: 'How to play' }).click();
  await page.locator('#feedback').fill('The crossing was a little hard to see.');
  await page.getByRole('button', { name: 'Save note' }).click();
  const download = page.waitForEvent('download'); await page.getByRole('button', { name: 'Export actions' }).click();
  expect((await download).suggestedFilename()).toMatch(/^switchyard-playtest-.*\.json$/);
  const events = await page.evaluate(() => JSON.parse(localStorage.getItem('switchyard:events:v1')));
  expect(events.some(e => e.event === 'player_note' && e.note.includes('crossing'))).toBe(true);
});
