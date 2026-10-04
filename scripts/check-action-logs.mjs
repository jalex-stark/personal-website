import assert from 'node:assert/strict';
import { chromium, webkit } from '../apps/little-trips/node_modules/@playwright/test/index.mjs';

const base = process.env.PLAYTEST_SITE_URL || 'http://127.0.0.1:4180';
for (const [name, engine] of [['Chromium', chromium], ['WebKit', webkit]]) {
  const browser = await engine.launch();
  try {
    for (const game of ['switchyard', 'little-trips', 'peel']) {
      const context = await browser.newContext(), page = await context.newPage(), errors = [], received = [];
      page.on('pageerror', error => errors.push(error.message));
      let fail = true;
      await context.route('**/api/game-actions', async route => {
        const batch = route.request().postDataJSON();
        if (fail) return route.fulfill({ status: 503, body: '{}' });
        received.push(batch);
        await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ accepted: batch.events.length }) });
      });
      await page.goto(`${base}/games/${game}/`);
      await page.waitForFunction(() => window.GameActionLog?.pending);
      if (game === 'switchyard') {
        await page.getByRole('button', { name: 'Show one connection' }).click();
        const track = await page.locator('#rack [data-tile]').first().boundingBox();
        await page.mouse.move(track.x + track.width / 2, track.y + track.height / 2);
        await page.mouse.down();
        await page.mouse.move(track.x + track.width / 2 - 80, track.y + track.height / 2, { steps: 8 });
        await page.mouse.up();
        await page.getByRole('button', { name: /How to play/ }).click();
        await page.locator('#feedback').pressSequentially('raw-secret-typing');
        await page.locator('#feedback').fill('Note sent explicitly');
        await page.getByRole('button', { name: 'Save note', exact: true }).click();
      } else if (game === 'little-trips') await page.getByRole('button', { name: 'A little nudge' }).click();
      else {
        await page.waitForSelector('#plane .tile');
        await page.locator('#plane .tile').first().click();
        await page.locator('#qa-open').click();
        await page.locator('#qa-toggle').click();
        await page.locator('#qa-close').click();
        await page.locator('#fit').click();
      }
      const before = await page.evaluate(async () => { await window.GameActionLog.flush(); return window.GameActionLog.pending(); });
      assert(before.length > 1, 'server failures retain actions');
      assert(before.some(row => row.event.action === 'session.open'));
      await page.reload(); // persisted outbox must survive leaving a session
      fail = false;
      await page.evaluate(() => window.GameActionLog.flush());
      await page.waitForFunction(async () => (await window.GameActionLog.pending()).length === 0);
      const events = received.flatMap(batch => batch.events);
      for (const row of before) assert(events.some(event => event.id === row.id), `missing ${row.event.action}`);
      const semantic = events.filter(event => !/^(session\.|input\.)/.test(event.action));
      assert(semantic.some(event => event.detail.board || event.detail.placements || event.detail.state), 'semantic actions include replay state');
      assert(events.some(event => event.action === 'input.pointerdown'));
      assert(events.some(event => event.action === 'input.click'));
      if (game === 'switchyard') assert(events.filter(event => event.action === 'input.pointermove').length >= 8, 'drag path retained');
      assert(!events.some(event => event.action === 'input.key' && /Note sent explicitly/.test(JSON.stringify(event))), 'typing is excluded');
      if (game === 'switchyard') assert(events.some(event => event.action === 'player_note' && event.detail.note === 'Note sent explicitly'));
      assert(!events.some(event => event.action === 'input.key' && event.detail.target?.id === 'feedback'));
      if (game === 'peel') assert(events.some(event => event.action === 'view.fit'), 'server recording continues when local QA panel pauses');
      assert.deepEqual(errors, []);
      if (game === 'switchyard') {
        const count = received.flatMap(batch => batch.events).length;
        await page.evaluate(async () => {
          for (let i = 0; i < 2600; i++) window.GameActionLog.record('retention.check', { i });
          await window.GameActionLog.flush();
        });
        await page.waitForFunction(async () => (await window.GameActionLog.pending()).length === 0);
        const retained = received.flatMap(batch => batch.events).slice(count).filter(event => event.action === 'retention.check');
        assert.equal(new Set(retained.map(event => event.id)).size, 2600, 'no recent-event cap or dropped batches');
        assert(received.every(batch => batch.events.length <= 100 && Buffer.byteLength(JSON.stringify(batch)) <= 48000));
      }
      await context.close();
      console.log(`${name}: ${game} retains failed uploads across reload and uploads complete actions`);
    }
    const context = await browser.newContext(), page = await context.newPage(), received = [];
    await context.route('**/api/game-actions', async route => { const batch = route.request().postDataJSON(); received.push(batch); await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ accepted: batch.events.length }) }); });
    await page.goto(`${base}/games/peel/challenges/14-shared-load.html`);
    await page.locator('#hint').click();
    await page.evaluate(() => window.GameActionLog.flush());
    assert(received.flatMap(batch => batch.events).some(event => event.action === 'hint' && event.detail.letters));
    await context.close();
    console.log(`${name}: Peel standalone work orders upload their board actions`);
  } finally { await browser.close(); }
}
