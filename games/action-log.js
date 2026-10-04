/* Shared production action journal. Loaded before each game's own scripts. */
(() => {
  const game = location.pathname.split('/')[2];
  if (!['peel', 'little-trips', 'switchyard'].includes(game) || window.GameActionLog) return;
  const uuid = () => crypto.randomUUID();
  let client;
  try { client = localStorage.getItem('games:player:v1') || uuid(); localStorage.setItem('games:player:v1', client); } catch { client = uuid(); }
  const session = uuid();
  let sequence = 0, writing = Promise.resolve(), flushing = false, retry = 0;
  const memory = new Map();
  const database = new Promise((resolve, reject) => {
    const req = indexedDB.open('games-action-outbox-v1', 1);
    req.onupgradeneeded = () => req.result.createObjectStore('pending', { keyPath: 'id' });
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
  // Keep playing if browser storage is unavailable; pending actions remain in memory.
  database.catch(() => {});
  const transact = async (mode, operation) => {
    const db = await database;
    return new Promise((resolve, reject) => {
      const tx = db.transaction('pending', mode), result = operation(tx.objectStore('pending'));
      tx.oncomplete = () => resolve(result?.result);
      tx.onerror = tx.onabort = () => reject(tx.error || Error('Action journal transaction failed'));
    });
  };
  const record = (action, detail = {}) => {
    let copy;
    try { copy = JSON.parse(JSON.stringify(detail)); } catch { return; }
    const event = { id: `${session}:${++sequence}`, sequence, timestamp: new Date().toISOString(), elapsedMs: Math.round(performance.now()), action, detail: copy };
    const row = { id: event.id, client, session, game, event };
    memory.set(row.id, row);
    writing = writing.then(() => transact('readwrite', store => store.put(row))).then(() => memory.delete(row.id)).catch(() => {});
  };
  const pending = async () => {
    await writing;
    let rows = [];
    try { rows = await transact('readonly', store => store.getAll()); } catch {}
    return [...new Map([...rows, ...memory.values()].map(row => [row.id, row])).values()];
  };
  const bodyFor = rows => JSON.stringify({ version: 1, client: rows[0].client, game: rows[0].game, session: rows[0].session, events: rows.map(row => row.event) });
  const choose = rows => {
    rows.sort((a, b) => a.event.timestamp.localeCompare(b.event.timestamp) || a.event.sequence - b.event.sequence);
    const first = rows[0], batch = [];
    for (const row of rows) {
      if (row.game !== first.game || row.client !== first.client || row.session !== first.session) continue;
      batch.push(row);
      if (new Blob([bodyFor(batch)]).size > 48000) { batch.pop(); break; }
      if (batch.length === 100) break;
    }
    return batch;
  };
  const flush = async () => {
    if (flushing) return;
    flushing = true;
    try {
      while (true) {
        const rows = await pending();
        if (!rows.length) break;
        const batch = choose(rows);
        if (!batch.length) throw Error('Action exceeds batch size');
        const response = await fetch('/api/game-actions', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: bodyFor(batch), keepalive: true, signal: AbortSignal.timeout(15000) });
        if (!response.ok || (await response.json()).accepted !== batch.length) throw Error('Action upload pending');
        // Never remove newly-recorded actions while acknowledging this batch.
        try { await transact('readwrite', store => { for (const row of batch) store.delete(row.id); }); }
        catch { if (batch.some(row => !memory.has(row.id))) throw Error('Acknowledgement pending'); }
        for (const row of batch) memory.delete(row.id);
      }
      retry = 0;
    } catch { retry = Math.min(retry + 1, 6); }
    finally { flushing = false; }
  };
  window.GameActionLog = { record, flush, pending, session, client };
  const target = node => {
    const element = node instanceof Element ? node.closest('[data-id],[data-item],[data-piece],[data-tile],[data-i],[data-action],button,a,input,textarea,select,[role="grid"],[role="gridcell"]') || node : null;
    return element ? { tag: element.tagName, id: element.id, data: { ...element.dataset }, label: element.getAttribute('aria-label') } : null;
  };
  const typing = node => node instanceof Element && !!node.closest('input,textarea,[contenteditable="true"]');
  const held = new Set();
  for (const type of ['pointerdown', 'pointermove', 'pointerup', 'pointercancel']) document.addEventListener(type, e => {
    if (typing(e.target)) return;
    if (type === 'pointerdown') held.add(e.pointerId);
    if (type === 'pointermove' && !held.has(e.pointerId)) return;
    record(`input.${type}`, { x: e.clientX, y: e.clientY, pointer: e.pointerType, pointerId: e.pointerId, buttons: e.buttons, target: target(e.target) });
    if (type === 'pointerup' || type === 'pointercancel') held.delete(e.pointerId);
  }, { capture: true, passive: true });
  document.addEventListener('click', e => { if (!typing(e.target)) record('input.click', { x: e.clientX, y: e.clientY, target: target(e.target) }); }, { capture: true, passive: true });
  const gameKeys = new Set(['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter', 'Escape', 'Tab', ' ', 'Delete', 'Backspace', 'Alt', 'r', 'z', 'y', 'w', 'a', 's', 'd', 'x']);
  document.addEventListener('keydown', e => {
    if (!typing(e.target) && gameKeys.has(e.key)) record('input.key', { key: e.key, shift: e.shiftKey, ctrl: e.ctrlKey, meta: e.metaKey, alt: e.altKey, repeat: e.repeat, target: target(e.target) });
  }, true);
  document.addEventListener('change', e => { if (e.target.matches('select,input[type="checkbox"]')) record('input.change', { target: target(e.target), value: e.target.type === 'checkbox' ? e.target.checked : e.target.value }); }, true);
  document.addEventListener('wheel', e => { if (!typing(e.target)) record('input.wheel', { x: e.clientX, y: e.clientY, dx: e.deltaX, dy: e.deltaY, ctrl: e.ctrlKey, target: target(e.target) }); }, { capture: true, passive: true });
  document.addEventListener('visibilitychange', () => { record('session.visibility', { hidden: document.hidden }); if (document.hidden) setTimeout(flush, 0); });
  window.addEventListener('pagehide', () => {
    record('session.pagehide');
    // Begin a final small upload synchronously: browsers may suspend asynchronous
    // IndexedDB reads during unload. Keep the outbox entries for safe later retry.
    const tail = choose([...memory.values()]);
    if (tail.length) fetch('/api/game-actions', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: bodyFor(tail), keepalive: true }).catch(() => {});
    flush();
  });
  window.addEventListener('pageshow', e => { if (e.persisted) { record('session.restored'); flush(); } });
  window.addEventListener('online', flush);
  window.addEventListener('error', e => record('app.error', { message: String(e.message).slice(0, 1000) }));
  window.addEventListener('unhandledrejection', e => record('app.rejection', { message: String(e.reason?.message || e.reason).slice(0, 1000) }));
  const tick = async () => { if (!document.hidden && sequence) await flush(); setTimeout(tick, 5000 * 2 ** retry); };
  setTimeout(tick, 5000);
  document.addEventListener('DOMContentLoaded', () => {
    const notice = document.createElement('p');
    notice.style.cssText = 'text-align:center;font:12px/1.6 sans-serif;color:#667267;padding:12px 20px;margin:0';
    notice.innerHTML = 'Gameplay actions and feedback are sent to improve these games. <a href="/games/playtesting.html">About playtesting</a>';
    document.body.append(notice);
  });
  record('session.open', { version: document.currentScript?.dataset.build || 'unknown', path: location.pathname, viewport: { width: innerWidth, height: innerHeight, pixelRatio: devicePixelRatio }, touch: navigator.maxTouchPoints > 0, automated: navigator.webdriver === true });
  flush();
})();
