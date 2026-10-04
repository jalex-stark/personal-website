import { levels } from './levels.js';
import { key, simulate, validPlacements } from './engine.js';

const $ = s => document.querySelector(s), app = $('#app'), ghost = $('#drag-ghost');
const SAVE = 'switchyard:save:v1', EVENTS = 'switchyard:events:v1';
const read = (name, fallback) => { try { return JSON.parse(localStorage.getItem(name)) ?? fallback; } catch { return fallback; } };
let saved = read(SAVE, {});
if (!saved || typeof saved !== 'object' || Array.isArray(saved)) saved = {};
saved.level = Number.isInteger(saved.level) ? Math.max(0, Math.min(levels.length - 1, saved.level)) : 0;
if (!saved.yards || typeof saved.yards !== 'object' || Array.isArray(saved.yards)) saved.yards = {};
for (const l of levels) {
  const y = saved.yards[l.id] || {};
  saved.yards[l.id] = { placements: validPlacements(l, y.placements) ? y.placements : {}, rotations: Object.fromEntries(l.tiles.map(t => [t.id, Number.isInteger(y.rotations?.[t.id]) && y.rotations[t.id] >= 0 && y.rotations[t.id] < 4 ? y.rotations[t.id] : 0])), complete: y.complete === true };
}
let events = read(EVENTS, []);
if (!Array.isArray(events)) events = [];
const session = crypto.randomUUID();
let sequence = 0, selected = null, carrying = false, undo = [], drag = null, running = false, runTimer, lastFrame = null, message = '', outcome = '', helpOpen = false, speed = 1;
const level = () => levels[saved.level], yard = () => saved.yards[level().id];
const tile = id => level().tiles.find(t => t.id === id);
const rotation = id => yard().placements[id]?.r ?? yard().rotations[id] ?? 0;
const save = () => { try { localStorage.setItem(SAVE, JSON.stringify(saved)); } catch {} };
const log = (event, detail = {}) => {
  events.push({ id: `${session}:${++sequence}`, session, sequence, timestamp: new Date().toISOString(), level: level().id, event, ...detail });
  events = events.slice(-1500);
  try { localStorage.setItem(EVENTS, JSON.stringify(events)); } catch {}
};
const names = { straight: 'Straight', curve: 'Curve', cross: 'Crossing' };

function art(type, r = 0) {
  const path = type === 'straight' ? 'M0 50H100' : type === 'curve' ? 'M50 0Q50 50 100 50' : 'M0 50H100M50 0V100';
  return `<svg class="rail-art" viewBox="0 0 100 100" aria-hidden="true"><g transform="rotate(${r * 90} 50 50)"><path class="ties" d="${path}"/><path class="rails" d="${path}"/><path class="rail-gap" d="${path}"/>${type === 'cross' ? '<circle cx="50" cy="50" r="11" fill="#d2bc88" stroke="#697865" stroke-width="3"/>' : ''}</g></svg>`;
}
function initialFrame() { return { step: 0, trains: level().trains.map(t => ({ id: t.id, ...t.source, dir: t.direction, state: 'waiting' })) }; }
function station(t, start) {
  const p = start ? t.source : t.target;
  return `<div class="station ${start ? 'departure' : 'destination'}" style="--train-color:${t.color};left:${(p.x + .5) / level().size * 100}%;top:${(p.y + .5) / level().size * 100}%" role="img" aria-label="${t.label} ${start ? 'departure' : 'platform'}" title="${t.label} ${start ? 'departs here' : 'arrives here'}"><span>${t.label[0]}</span>${!start ? '<small>HOME</small>' : ''}</div>`;
}
function trainArt(t, state) {
  return `<g class="train" data-train="${t.id}" style="transform:translate(${(state.x + .5) * 100}px,${(state.y + .5) * 100}px)"><g class="engine" transform="rotate(${state.dir * 90})"><rect x="-16" y="-21" width="32" height="42" rx="10" fill="${t.color}" stroke="#fff9e8" stroke-width="3"/><rect x="-10" y="-12" width="20" height="11" rx="3" fill="#fff9e8"/><path d="M-9 10H9" stroke="#fff9e8" stroke-width="4" stroke-linecap="round"/></g></g>`;
}

function render() {
  const l = level(), y = yard(), frame = lastFrame || initialFrame(), byCell = new Map(Object.entries(y.placements).map(([id, p]) => [key(p.x, p.y), id]));
  const blocked = new Set(l.obstacles.map(([x, y]) => key(x, y)));
  let cells = '';
  for (let cy = 0; cy < l.size; cy++) for (let cx = 0; cx < l.size; cx++) {
    const id = byCell.get(key(cx, cy)), pond = blocked.has(key(cx, cy));
    cells += `<button class="cell ${pond ? 'pond' : ''} ${id ? 'track' : ''} ${id === selected ? 'selected' : ''}" data-cell="${cx},${cy}" ${id ? `data-tile="${id}"` : ''} ${pond || running ? 'disabled' : ''} aria-label="${pond ? 'Pond' : id ? `${names[tile(id).type]} track` : 'Empty square'}, column ${cx + 1}, row ${cy + 1}">${id ? art(tile(id).type, rotation(id)) : pond ? '<svg viewBox="0 0 100 100" aria-hidden="true"><path d="M18 37q10-8 20 0t20 0t20 0M23 58q10-8 20 0t20 0" fill="none" stroke="#86afbb" stroke-width="4" stroke-linecap="round"/></svg>' : '<span class="socket"></span>'}</button>`;
  }
  const remaining = l.tiles.filter(t => !y.placements[t.id]).length;
  const rackMarkup = [...new Set(l.tiles.map(t => t.type))].map(type => {
    const available = l.tiles.filter(t => t.type === type && !y.placements[t.id]);
    const t = available[0] || l.tiles.find(t => t.type === type);
    return `<div class="rack-type"><button class="track rack-track ${selected === t.id ? 'selected' : ''} ${available.length ? '' : 'packed'}" data-tile="${t.id}" ${!available.length || running ? 'disabled' : ''} aria-label="${names[type]} track ${l.tiles.indexOf(t) + 1}">${art(type, rotation(t.id))}</button><span>${names[type]} <b>${available.length}</b></span></div>`;
  }).join('');
  app.innerHTML = `<main><nav><a href="/games/">← Games</a><button class="plain-button" data-action="help">How to play <span class="help-icon">?</span></button></nav><header><p class="eyebrow"><span class="signal-dot"></span> A SMALL PLAN. A SMOOTH DEPARTURE.</p><h1>Switchyard<span>.</span></h1><p class="intro">Make every connection.</p></header><div class="workbench"><section class="yard-panel"><div class="level-heading"><div><p class="eyebrow">YARD ${saved.level + 1} OF ${levels.length}</p><h2 id="yard-title">${l.title}</h2></div><label class="yard-picker"><span class="sr-only">Choose a yard</span><select id="yard-picker">${levels.map((l, i) => `<option value="${i}" ${i === saved.level ? 'selected' : ''}>${saved.yards[l.id].complete ? '✓ ' : ''}${i + 1}. ${l.title}</option>`).join('')}</select></label></div><p class="yard-note">${l.note}</p><div class="board-wrap"><div id="board" style="--cols:${l.size}"><div id="cells" role="group" aria-label="Railway yard">${cells}</div>${l.trains.map(t => station(t, true) + station(t, false)).join('')}<svg id="trains" viewBox="0 0 ${l.size * 100} ${l.size * 100}" aria-hidden="true" style="--beat:${550 / speed}ms">${l.trains.map(t => trainArt(t, frame.trains.find(s => s.id === t.id))).join('')}<circle id="fail-marker" r="28" hidden/></svg></div></div><div class="timetable">${l.trains.map(t => `<span style="--train-color:${t.color}"><i></i><strong>${t.label}</strong> <small>${t.delay ? `after ${t.delay} beat` : 'leaves now'}</small></span>`).join('')}</div><div class="run-controls"><button class="run-button ${running ? 'running' : ''}" data-action="run" id="run">${running ? '■ Stop' : '▶ Play the plan'}</button><button class="speed-button" data-action="speed" ${running ? 'disabled' : ''} aria-label="Playback speed ${speed} times">${speed}×</button><button class="mobile-edit" data-action="turn" ${!selected || running ? 'disabled' : ''}>↻ Turn</button><button class="mobile-edit" data-action="undo" ${!undo.length || running ? 'disabled' : ''}>↶ Undo</button><span id="beat-count">${running ? `Beat ${frame.step}` : 'Ready.'}</span></div><p id="message" class="message ${outcome}" role="status">${message || 'Drag a track into place. Turn it until the rails connect.'}</p>${outcome === 'success' ? `<div class="finish-row"><span>✓ Connection made</span>${saved.level < levels.length - 1 ? '<button class="next-button" data-action="next">Next yard →</button>' : '<strong>All five yards are ready. Beautifully done.</strong>'}</div>` : ''}</section><aside><section class="rack-panel" id="rack"><div class="rack-heading"><h2>Your tracks</h2><span>${remaining} left</span></div><p class="rack-note">Pick up a piece. Give it a turn. See where it leads.</p><div class="rack">${rackMarkup}</div><div class="edit-controls"><button data-action="turn" id="turn" ${!selected || running ? 'disabled' : ''}>↻ Turn <kbd>R</kbd></button><button data-action="remove" ${!selected || !y.placements[selected] || running ? 'disabled' : ''}>Take out</button><button data-action="undo" ${!undo.length || running ? 'disabled' : ''}>↶ Undo</button></div><p class="selection-note">${selected ? `${names[tile(selected).type]} selected. Tap a square to place it.` : 'Tap a track, then a square. Dragging works too.'}</p></section><section class="small-tools"><button data-action="hint">Show one connection <span>↗</span></button><button data-action="reset">Clear this yard <span>↺</span></button></section><p class="yard-footnote">Matching colors mark each train’s home. Crossings go straight through. The trains move one square per beat.</p><p class="progress">${levels.filter(l => saved.yards[l.id].complete).length} / ${levels.length} connections made</p></aside></div><footer>Planning is the game. Take all the time you need.</footer></main>${helpOpen ? `<dialog aria-labelledby="help-title"><button class="dialog-close" data-action="close" aria-label="Close instructions">×</button><p class="eyebrow">WELCOME TO THE YARD</p><h2 id="help-title">A way home for everyone.</h2><p>Join each colored engine to its matching platform. Drag tracks from the tray, or tap a track and then a square. Select a piece and choose <strong>Turn</strong> to rotate it. Moving onto another track swaps the pieces.</p><p>Press <strong>Play the plan</strong> when you’re ready. Each train moves one square per beat. Crossings carry trains straight through; two trains arriving together, or meeting head-on, collide. The timetable shows when each train leaves. Stop and rearrange as often as you like.</p><p>Drag a track back to the tray to take it out. Undo reverses your edits. <strong>Show one connection</strong> fits one piece into a working plan; it may return an overlapping track to the tray.</p><p class="keyboard-note">Keyboard: Tab to a track and Enter to select; Tab to a square and Enter to place. R turns, Delete takes out, Z undoes, Escape clears or stops.</p><hr><p class="privacy-note">Progress and recent actions stay in this browser. Export your actions to help improve the game.</p><label for="feedback">What felt awkward?</label><textarea id="feedback" rows="2" maxlength="1000" placeholder="A small note helps."></textarea><div class="feedback-actions"><button data-action="note">Save note</button><button data-action="export">Export actions</button></div><p id="feedback-status" role="status"></p></dialog>` : ''}`;
  if (helpOpen) $('dialog').showModal();
  showFrame(frame);
}

function showFrame(frame) {
  for (const t of frame.trains) {
    const node = $(`[data-train="${t.id}"]`);
    if (!node) continue;
    node.style.transform = `translate(${(t.x + .5) * 100}px,${(t.y + .5) * 100}px)`;
    node.querySelector('.engine').setAttribute('transform', `rotate(${t.dir * 90})`);
    node.classList.toggle('arrived', t.state === 'arrived');
  }
  if (running) $('#beat-count').textContent = `Beat ${frame.step}`;
  const mark = $('#fail-marker');
  if (frame.failure?.at) {
    mark.setAttribute('cx', (frame.failure.at.x + .5) * 100); mark.setAttribute('cy', (frame.failure.at.y + .5) * 100); mark.removeAttribute('hidden');
  }
}
function stopRun(clear = true) {
  clearTimeout(runTimer);
  if (running) log('run_stopped', { beat: lastFrame?.step || 0 });
  running = false;
  if (clear) { lastFrame = null; outcome = ''; }
}
function commit(change, event, detail = {}) {
  stopRun();
  undo.push(structuredClone({ placements: yard().placements, rotations: yard().rotations }));
  undo = undo.slice(-100);
  change(); save(); log(event, { ...detail, placements: structuredClone(yard().placements) }); render();
}
function select(id, grip = { x: .5, y: .5 }) {
  stopRun(); message = '';
  selected = selected === id && carrying ? null : id;
  carrying = !!selected;
  log('track_selected', { tile: id, grip }); render();
}
function place(id, p, r = rotation(id), event = 'track_placed', detail = {}) {
  const other = Object.keys(yard().placements).find(k => k !== id && yard().placements[k].x === p.x && yard().placements[k].y === p.y), from = yard().placements[id];
  message = other ? 'Swapped those tracks.' : ''; selected = id; carrying = false;
  commit(() => {
    if (other) {
      if (from) yard().placements[other] = { ...from, r: rotation(other) };
      else delete yard().placements[other];
    }
    yard().placements[id] = { ...p, r }; yard().rotations[id] = r;
  }, event, { tile: id, target: p, swapped: other || null, ...detail });
}
function turn() {
  if (drag?.moved) {
    drag.r = (drag.r + 1) % 4; drag.grip = { x: 1 - drag.grip.y, y: drag.grip.x };
    ghost.innerHTML = art(tile(drag.id).type, drag.r); updateDrag(drag.pointer);
    log('held_track_turned', { tile: drag.id, rotation: drag.r }); return;
  }
  if (!selected || running) return;
  const id = selected, r = (rotation(id) + 1) % 4;
  message = '';
  commit(() => { yard().rotations[id] = r; if (yard().placements[id]) yard().placements[id].r = r; }, 'track_turned', { tile: id, rotation: r });
}
function takeOut() {
  if (!selected || !yard().placements[selected]) return;
  const id = selected; message = '';
  commit(() => { delete yard().placements[id]; }, 'track_removed', { tile: id });
}
function undoEdit() {
  if (!undo.length) return;
  stopRun(); Object.assign(yard(), undo.pop()); message = ''; save(); log('undo'); render();
}
function useLevel(index) {
  stopRun(); selected = null; undo = []; message = ''; saved.level = index; save(); log('yard_opened'); render();
}
function hint() {
  const l = level(), id = l.tiles.find(t => JSON.stringify(yard().placements[t.id]) !== JSON.stringify(l.solution[t.id]))?.id;
  if (!id) { message = 'The connections are ready. Play the plan.'; render(); return; }
  message = 'One little connection.';
  const p = l.solution[id];
  place(id, { x: p.x, y: p.y }, p.r, 'hint_used');
  message = 'One little connection.'; $('#message').textContent = message;
}
function play() {
  if (running) { stopRun(); message = 'Back to the drawing board.'; render(); return; }
  const plan = simulate(level(), yard().placements);
  selected = null; running = true; message = 'Let’s see where this goes.'; outcome = ''; lastFrame = plan.frames[0]; render();
  log('plan_played', { placements: structuredClone(yard().placements) });
  let i = 1;
  const beat = 550 / speed;
  const step = () => {
    if (i < plan.frames.length) {
      lastFrame = plan.frames[i++]; showFrame(lastFrame); runTimer = setTimeout(step, beat); return;
    }
    running = false; outcome = plan.ok ? 'success' : 'failure';
    message = plan.ok ? 'Everyone made their connection.' : plan.message;
    if (plan.ok) { yard().complete = true; save(); }
    log('plan_finished', { outcome: plan.ok ? 'arrived' : plan.reason, beat: lastFrame.step, ...(plan.at ? { location: plan.at } : {}) }); render();
  };
  runTimer = setTimeout(step, 50);
}

app.addEventListener('click', e => {
  const action = e.target.closest('[data-action]')?.dataset.action;
  if (action) {
    if (action === 'run') play();
    else if (action === 'turn') turn();
    else if (action === 'remove') takeOut();
    else if (action === 'undo') undoEdit();
    else if (action === 'hint') hint();
    else if (action === 'reset') { selected = null; message = ''; commit(() => { yard().placements = {}; }, 'yard_cleared'); }
    else if (action === 'next') useLevel(Math.min(levels.length - 1, saved.level + 1));
    else if (action === 'speed') { speed = speed === 1 ? 2 : 1; render(); }
    else if (action === 'help') { stopRun(); helpOpen = true; render(); }
    else if (action === 'close') { helpOpen = false; render(); }
    else if (action === 'note') {
      const note = $('#feedback').value.trim(); if (!note) return;
      log('player_note', { note, placements: structuredClone(yard().placements) }); $('#feedback').value = ''; $('#feedback-status').textContent = 'Saved with your recent actions.';
    } else if (action === 'export') {
      log('actions_exported'); const url = URL.createObjectURL(new Blob([JSON.stringify({ game: 'switchyard', version: 1, events }, null, 2)], { type: 'application/json' }));
      const a = document.createElement('a'); a.href = url; a.download = `switchyard-playtest-${new Date().toISOString().slice(0, 10)}.json`; a.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
    return;
  }
  if (running) return;
  const button = e.target.closest('[data-cell],[data-tile]'); if (!button || button.disabled) return;
  if (button.dataset.tile) {
    if (e.detail === 0) {
      if (selected && carrying && selected !== button.dataset.tile && button.dataset.cell) place(selected, Object.fromEntries(['x', 'y'].map((k, i) => [k, Number(button.dataset.cell.split(',')[i])])));
      else select(button.dataset.tile);
    }
    return;
  }
  if (button.dataset.cell && selected) {
    const [x, y] = button.dataset.cell.split(',').map(Number); place(selected, { x, y });
  }
});
app.addEventListener('change', e => { if (e.target.id === 'yard-picker') useLevel(Number(e.target.value)); });
app.addEventListener('cancel', () => { helpOpen = false; }, true);

function targetAt(e) {
  const rect = $('#cells').getBoundingClientRect(), unit = rect.width / level().size;
  const x = Math.round((e.clientX - rect.left) / unit - drag.grip.x), y = Math.round((e.clientY - rect.top) / unit - drag.grip.y);
  const inside = x >= 0 && y >= 0 && x < level().size && y < level().size;
  return { x, y, valid: inside && !level().obstacles.some(([a, b]) => a === x && b === y) };
}
function updateDrag(e) {
  if (!drag?.moved) return;
  drag.pointer = e;
  const unit = $('#cells').getBoundingClientRect().width / level().size;
  ghost.style.width = `${unit}px`; ghost.style.height = `${unit}px`;
  ghost.style.transform = `translate(${e.clientX - drag.grip.x * unit}px,${e.clientY - drag.grip.y * unit}px)`;
  const p = targetAt(e); ghost.classList.toggle('blocked', !p.valid);
  const target = `${p.x},${p.y}`;
  if (target !== drag.lastTarget) { drag.targetChanges++; drag.lastTarget = target; }
  for (const c of app.querySelectorAll('.cell.drop-target')) c.classList.remove('drop-target', 'blocked');
  const cell = $(`[data-cell="${target}"]`);
  if (cell) { cell.classList.add('drop-target'); cell.classList.toggle('blocked', !p.valid); }
}
app.addEventListener('pointerdown', e => {
  if (running || helpOpen || drag || (e.pointerType === 'mouse' && e.button !== 0)) return;
  const button = e.target.closest('[data-tile]'); if (!button || button.disabled) return;
  const rect = button.getBoundingClientRect();
  drag = { id: button.dataset.tile, r: rotation(button.dataset.tile), grip: { x: (e.clientX - rect.left) / rect.width, y: (e.clientY - rect.top) / rect.height }, start: { x: e.clientX, y: e.clientY }, pointer: e, pointerId: e.pointerId, started: performance.now(), moved: false, targetChanges: 0, input: e.pointerType };
});
document.addEventListener('pointermove', e => {
  if (!drag || e.pointerId !== drag.pointerId) return;
  if (!drag.moved && Math.hypot(e.clientX - drag.start.x, e.clientY - drag.start.y) > 5) {
    drag.moved = true; app.setPointerCapture(e.pointerId); ghost.innerHTML = art(tile(drag.id).type, drag.r); ghost.classList.add('visible');
    app.querySelectorAll(`[data-tile="${drag.id}"]`).forEach(b => b.classList.add('lifting'));
  }
  if (drag.moved) { e.preventDefault(); updateDrag(e); }
}, { passive: false });
document.addEventListener('pointerup', e => {
  if (!drag || e.pointerId !== drag.pointerId) return;
  const d = drag;
  if (!d.moved) {
    drag = null;
    const p = yard().placements[d.id];
    if (selected && carrying && selected !== d.id && p) place(selected, { x: p.x, y: p.y });
    else select(d.id, d.grip);
    return;
  }
  const p = targetAt(e), rack = $('#rack').getBoundingClientRect();
  const detail = { input: d.input, grip: d.grip, durationMs: Math.round(performance.now() - d.started), distance: Math.round(Math.hypot(e.clientX - d.start.x, e.clientY - d.start.y)), targetChanges: d.targetChanges };
  ghost.classList.remove('visible'); drag = null;
  if (app.hasPointerCapture(e.pointerId)) app.releasePointerCapture(e.pointerId);
  if (p.valid) place(d.id, { x: p.x, y: p.y }, d.r, 'track_dragged', detail);
  else if (yard().placements[d.id] && e.clientX >= rack.left && e.clientX <= rack.right && e.clientY >= rack.top && e.clientY <= rack.bottom) {
    selected = d.id; commit(() => { delete yard().placements[d.id]; }, 'track_returned', { tile: d.id, ...detail });
  } else { log('drag_rejected', { tile: d.id, target: p, ...detail }); message = 'That piece goes on a free patch of ground.'; render(); }
});
function cancelDrag() {
  if (!drag) return;
  log('drag_cancelled', { tile: drag.id });
  ghost.classList.remove('visible'); drag = null; render();
}
document.addEventListener('pointercancel', cancelDrag);
window.addEventListener('blur', () => { if (drag) cancelDrag(); });
document.addEventListener('keydown', e => {
  if (e.target.matches('input,textarea,select')) return;
  if (e.key === 'Escape') {
    if (helpOpen) { helpOpen = false; render(); }
    else { if (drag) cancelDrag(); stopRun(); selected = null; message = ''; render(); }
  } else if (e.key.toLowerCase() === 'r') { e.preventDefault(); turn(); }
  else if (e.key.toLowerCase() === 'z' && !helpOpen) { e.preventDefault(); undoEdit(); }
  else if ((e.key === 'Delete' || e.key === 'Backspace') && !helpOpen) { e.preventDefault(); takeOut(); }
});
render(); log('game_opened');
