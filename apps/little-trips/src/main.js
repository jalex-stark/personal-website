import { levels } from './levels.js';
import { rotate, dimensions, centerOfMass, absoluteCells, canPlace, isComplete, getHint } from './engine.js';
import { icon, pieceArt, landscape } from './art.js';
import { loadSave, saveGame, track, exportEvents } from './storage.js';
import { sound } from './audio.js';

const app = document.querySelector('#app');
const ghost = document.querySelector('#drag-ghost');
const loaded = loadSave();
const saved = loaded && typeof loaded === 'object' && !Array.isArray(loaded) ? loaded : {};
const progress = saved.trips && typeof saved.trips === 'object' ? saved.trips : {};
const completed = new Set(Array.isArray(saved.completed) ? saved.completed.filter(id => levels.some(l => l.id === id)) : []);
let levelIndex = Math.max(0, levels.findIndex(l => l.id === saved.current));
let level, placements = {}, rotations = {}, selected = null, history = [], hint = null;
let moves = 0, hints = 0, activeMs = 0, clockStart = performance.now();
let muted = saved.muted === true, phase = 'playing', drag = null, suppressClickUntil = 0;
let cursor = { x: 0, y: 0 }, status = '', statusTimer, finishTimer;
let selectionGrip = null;
let floatingRotation = null;
let dragFrame = 0, previewKey = '';
const copy = value => structuredClone(value);

app.innerHTML = `
  <header class="topbar">
    <a class="brand" href="#" aria-label="Little Trips, choose a trip">${icon('suitcase', 30)}<span>little trips<span class="brand-dot">.</span></span></a>
    <div class="top-actions">
      <button class="journal-button" data-action="trips" aria-label="Your little trips">${icon('plane', 17)}<span>Your little trips</span><span id="trip-count"></span></button>
      <button class="icon-button" id="sound-button" data-action="sound" title="Toggle sound"></button>
      <button class="icon-button" data-action="help" aria-label="How to play">${icon('help', 21)}</button>
    </div>
  </header>
  <main>
    <section class="trip-heading" aria-labelledby="trip-title">
      <div class="eyebrow" id="trip-eyebrow"></div>
      <h1 id="trip-title"></h1><p id="trip-subtitle"></p>
    </section>
    <div class="game-layout">
      <section class="packing-stage" aria-label="Your suitcase">
        <div class="postcard" id="postcard"></div>
        <div class="case-holder" id="case-holder">
          <div class="case-handle"></div>
          <div class="suitcase" id="suitcase">
            <span class="case-corner corner-tl"></span><span class="case-corner corner-tr"></span>
            <span class="case-corner corner-bl"></span><span class="case-corner corner-br"></span>
            <div id="board" class="board" role="grid" tabindex="0" aria-label="Suitcase packing grid"></div>
            <div class="closed-lid" aria-hidden="true"><div class="lid-straps"></div><span class="lid-sticker">a little<br><i>adventure</i></span>${icon('plane', 48)}</div>
          </div>
          <div class="case-tag"><span id="destination"></span><span class="tag-hole"></span></div>
        </div>
        <div class="packing-progress"><span id="packed-count"></span><span class="progress-track"><span id="progress-fill"></span></span><span id="packing-caption">A place for every little thing</span></div>
        <div class="stage-actions">
          <button class="soft-button" data-action="hint">${icon('hint', 19)}<span>A little nudge</span></button>
          <button class="soft-button" data-action="undo" id="undo-button">${icon('undo', 18)}<span>Undo</span></button>
          <button class="icon-button" data-action="restart" title="Start this trip again" aria-label="Start this trip again">${icon('restart', 19)}</button>
        </div>
      </section>
      <section class="blanket-panel" aria-labelledby="blanket-title">
        <div class="blanket-heading"><div><span class="eyebrow">THE ESSENTIALS</span><h2 id="blanket-title">On your blanket</h2></div><span id="remaining-count" class="count-bubble"></span></div>
        <p class="blanket-instruction" id="blanket-instruction">Drag a little thing into your suitcase.</p>
        <div id="tray" class="tray"></div>
        <div id="selection-controls" class="selection-controls"></div>
        <div class="little-note"><span class="note-sparkle">✳</span><p id="trip-note"></p></div>
      </section>
    </div>
    <div class="bottom-bar"><p id="message" role="status" aria-live="polite"></p><button id="zip-button" class="primary-button" data-action="zip" hidden>Zip up & go ${icon('arrow', 18)}</button></div>
  </main>
  <footer class="footer"><span>Small suitcase. Big little adventure.</span><span>Take your time <span class="footer-star">✧</span></span></footer>
  <dialog id="modal" class="modal"></dialog>
`;

const board = document.querySelector('#board');
const tray = document.querySelector('#tray');
const modal = document.querySelector('#modal');

function activeTime() { return activeMs + (document.hidden ? 0 : performance.now() - clockStart); }
function record(event, extra = {}) {
  track(event, { level: level.id, moves, hints, activeMs: Math.round(activeTime()), ...extra });
}
function persist() {
  if (level) progress[level.id] = { placements, rotations, moves, hints, activeMs: activeTime() };
  saveGame({ version: 1, current: level?.id, trips: progress, completed: [...completed], muted });
}
function snapshot() { return copy({ placements, rotations }); }
function remember() { history.push(snapshot()); if (history.length > 100) history.shift(); }

function startTrip(index) {
  if (level) { record('level_leave', { packed: Object.keys(placements).length }); persist(); }
  clearTimeout(finishTimer); clearTimeout(statusTimer); cancelDrag(); cancelFloatingRotation('trip_change');
  levelIndex = index; level = levels[index];
  const prior = progress[level.id];
  placements = {};
  if (prior?.placements && typeof prior.placements === 'object') {
    for (const item of level.items) {
      const p = prior.placements[item.id];
      if (p && canPlace(level, placements, item.id, p)) placements[item.id] = { x: p.x, y: p.y, rotation: ((p.rotation % 4) + 4) % 4 };
    }
  }
  rotations = Object.fromEntries(level.items.map(item => [item.id, placements[item.id]?.rotation ?? (Number.isInteger(prior?.rotations?.[item.id]) ? ((prior.rotations[item.id] % 4) + 4) % 4 : item.initialRotation)]));
  moves = Number.isFinite(prior?.moves) ? prior.moves : 0;
  hints = Number.isFinite(prior?.hints) ? prior.hints : 0;
  activeMs = Number.isFinite(prior?.activeMs) ? prior.activeMs : 0; clockStart = performance.now();
  history = []; selected = null; selectionGrip = null; hint = null; phase = 'playing'; cursor = { x: 0, y: 0 };
  status = 'No rush. Everything will find its place.';
  document.querySelector('#trip-title').textContent = level.title;
  document.querySelector('#trip-subtitle').textContent = level.subtitle;
  document.querySelector('#trip-eyebrow').innerHTML = `<span class="tiny-line"></span> LITTLE TRIP ${level.stamp} OF 05 <span class="tiny-line"></span>`;
  document.querySelector('#postcard').innerHTML = `${landscape(level.theme)}<span class="postcard-caption">${level.destination.toLowerCase()}</span><span class="postcard-stamp">${level.stamp}</span>`;
  document.querySelector('#destination').textContent = level.destination;
  document.querySelector('#trip-note').textContent = level.note;
  board.style.setProperty('--cols', level.width); board.style.setProperty('--rows', level.height);
  document.querySelector('#case-holder').style.setProperty('--cols', level.width);
  board.setAttribute('aria-rowcount', level.height); board.setAttribute('aria-colcount', level.width);
  record('level_start', { resumed: Object.keys(placements).length > 0, board: snapshot() });
  render(); persist();
}

function render() {
  const focusedId = document.activeElement?.dataset?.piece;
  const focusedWasTray = focusedId && document.activeElement.classList.contains('item-card');
  const boardFocused = document.activeElement === board;
  const ready = !floatingRotation && isComplete(level, placements);
  const packed = Object.keys(placements).length - (floatingRotation ? 1 : 0);
  document.querySelector('#suitcase').classList.toggle('zipping', phase === 'zipping');
  document.querySelector('#trip-count').textContent = `${completed.size}/5`;
  document.querySelector('#sound-button').innerHTML = icon(muted ? 'mute' : 'sound', 20);
  document.querySelector('#sound-button').setAttribute('aria-label', muted ? 'Turn sound on' : 'Turn sound off');
  document.querySelector('#sound-button').setAttribute('aria-pressed', String(!muted));
  document.querySelector('#packed-count').textContent = `${packed} / ${level.items.length} packed`;
  document.querySelector('#remaining-count').textContent = String(level.items.length - packed);
  document.querySelector('#progress-fill').style.width = `${packed / level.items.length * 100}%`;
  document.querySelector('#packing-caption').textContent = ready ? 'A perfect little fit' : 'A place for every little thing';
  document.querySelector('#undo-button').disabled = (!history.length && !floatingRotation) || phase !== 'playing';
  document.querySelector('#zip-button').hidden = !ready || phase !== 'playing';
  document.querySelector('#blanket-instruction').textContent = ready ? 'Everything is in. Shall we go?' : 'Drag an item, or tap one and then a square.';
  board.innerHTML = Array.from({ length: level.width * level.height }, (_, i) => {
    const x = i % level.width, y = Math.floor(i / level.width);
    return `<div class="board-cell" id="cell-${x}-${y}" role="gridcell" aria-label="Row ${y + 1}, column ${x + 1}" data-x="${x}" data-y="${y}"></div>`;
  }).join('') + '<div class="board-preview" id="board-preview" aria-hidden="true"></div>';
  for (const item of level.items) {
    const floating = floatingRotation?.id === item.id;
    const p = floating ? floatingRotation.placement : placements[item.id];
    if (!p) continue;
    const cells = rotate(item.cells, p.rotation), { width, height } = dimensions(cells);
    const el = document.createElement('button');
    el.className = `packed-piece ${selected === item.id ? 'selected' : ''} ${floating ? 'floating-piece' : ''}`;
    if (floating) el.dataset.floating = 'true';
    el.dataset.piece = item.id; el.dataset.source = 'board';
    el.setAttribute('aria-label', floating ? `${item.name}, in hand. Move to place.` : `${item.name}, packed. Select to move or rotate.`);
    el.style.cssText = `left:calc(${p.x} * var(--cell));top:calc(${p.y} * var(--cell));width:calc(${width} * var(--cell));height:calc(${height} * var(--cell));`;
    el.innerHTML = pieceArt(item, cells);
    // Transparent corners must pass through to the empty grid square below.
    el.style.clipPath = `polygon(${boundaryPoints(cells).map(([x, y]) => `${x / width * 100}% ${y / height * 100}%`).join(',')})`;
    board.append(el);
  }
  tray.innerHTML = level.items.filter(i => !placements[i.id]).map(item => {
    const cells = rotate(item.cells, rotations[item.id]), { width, height } = dimensions(cells);
    return `<button class="item-card ${selected === item.id ? 'selected' : ''}" data-piece="${item.id}" data-source="tray" aria-label="${item.name}. Select to pack." aria-pressed="${selected === item.id}"><span class="item-art-slot"><span class="piece-visual" style="width:calc(${width} * var(--tray-cell));height:calc(${height} * var(--tray-cell));">${pieceArt(item, cells)}</span></span><span class="item-name">${item.name}</span><span class="item-cells">${item.cells.length} little squares</span></button>`;
  }).join('') || `<div class="empty-blanket">${icon('sparkle', 42)}<h3>${floatingRotation ? 'One little thing in hand.' : 'All tucked in.'}</h3><p>${floatingRotation ? 'Find it a spot, or return it here.' : 'Your little adventure awaits.'}</p></div>`;
  previewKey = '';
  renderSelection();
  document.querySelector('#message').textContent = ready ? 'Everything fits! Zip up your suitcase to finish this trip.' : status;
  if (hint) showPreview(hint.itemId, hint.placement, true);
  if (floatingRotation) updateFloatingPosition(floatingRotation.placement);
  if (boardFocused) { board.focus({ preventScroll: true }); updateCursor(true); }
  if (focusedWasTray) tray.querySelector(`[data-piece="${focusedId}"]`)?.focus({ preventScroll: true });
}

function renderSelection() {
  for (const el of document.querySelectorAll('[data-piece]')) {
    el.classList.toggle('selected', el.dataset.piece === selected);
    if (el.dataset.source === 'tray') el.setAttribute('aria-pressed', String(el.dataset.piece === selected));
  }
  const item = level.items.find(i => i.id === selected);
  document.querySelector('#selection-controls').innerHTML = item ? `<span class="selected-name">${item.name}</span><button class="rotate-button" data-action="rotate">${icon('rotate', 18)} Turn <kbd>R</kbd></button>${placements[item.id] ? '<button class="remove-button" data-action="remove">Take out</button>' : ''}` : `<span class="selection-placeholder">${icon('rotate', 17)} Select an item to turn it</span>`;
}

function boundaryPoints(cells) {
  const set = new Set(cells.map(c => c.join(','))), edges = [];
  for (const [x, y] of cells) {
    if (!set.has(`${x},${y - 1}`)) edges.push([[x, y], [x + 1, y]]);
    if (!set.has(`${x + 1},${y}`)) edges.push([[x + 1, y], [x + 1, y + 1]]);
    if (!set.has(`${x},${y + 1}`)) edges.push([[x + 1, y + 1], [x, y + 1]]);
    if (!set.has(`${x - 1},${y}`)) edges.push([[x, y + 1], [x, y]]);
  }
  const points = [edges[0][0]]; let end = edges[0][1];
  while (end.join(',') !== points[0].join(',') && points.length <= edges.length) {
    points.push(end); end = edges.find(([start]) => start.join(',') === end.join(','))[1];
  }
  return points;
}

function message(text, temporary = false) {
  clearTimeout(statusTimer); status = text;
  document.querySelector('#message').textContent = text;
  if (temporary) statusTimer = setTimeout(() => {
    status = selected ? 'Turn it if you like. Then find its little spot.' : 'No rush. Everything will find its place.';
    document.querySelector('#message').textContent = status;
  }, 3200);
}

function rotationFor(id) { return floatingRotation?.id === id ? floatingRotation.placement.rotation : rotations[id]; }

function gripFor(id) {
  const rotation = rotationFor(id);
  if (selectionGrip?.id !== id || selectionGrip.rotation !== rotation) {
    const item = level.items.find(i => i.id === id);
    selectionGrip = { id, ...centerOfMass(rotate(item.cells, rotation)), rotation, kind: 'mass' };
  }
  return selectionGrip;
}

function selectPiece(id, grip = null) {
  if (phase !== 'playing' || !level.items.some(i => i.id === id)) return;
  if (floatingRotation && floatingRotation.id !== id) cancelFloatingRotation('select_other');
  if (selected !== id) { hint = null; record('piece_select', { item: id, source: placements[id] ? 'board' : 'tray' }); }
  selected = id;
  if (grip) selectionGrip = { id, ...grip, rotation: rotationFor(id) };
  else gripFor(id);
  renderSelection();
  clearPreview();
  message(`${level.items.find(i => i.id === id).name}: turn it, drag it, or tap a square to place it.`);
}

function updateFloatingPosition(placement) {
  if (!floatingRotation) return;
  floatingRotation.placement = { ...placement };
  const grip = gripFor(floatingRotation.id);
  cursor = { x: Math.max(0, Math.min(level.width - 1, Math.floor(placement.x + grip.x))), y: Math.max(0, Math.min(level.height - 1, Math.floor(placement.y + grip.y))) };
  const el = board.querySelector('[data-floating]');
  if (el) {
    el.style.left = `calc(${placement.x} * var(--cell))`; el.style.top = `calc(${placement.y} * var(--cell))`;
    el.classList.toggle('blocked', !canPlace(level, placements, floatingRotation.id, placement));
  }
  showPreview(floatingRotation.id, placement);
}

function cancelFloatingRotation(reason) {
  if (!floatingRotation) return false;
  record('rotation_cancelled', { item: floatingRotation.id, reason, target: floatingRotation.placement });
  floatingRotation = null; selectionGrip = null;
  render();
  return true;
}

function placePiece(id, placement, method = 'tap') {
  if (phase !== 'playing') return false;
  if (!canPlace(level, placements, id, placement)) {
    const outside = absoluteCells(level.items.find(i => i.id === id), placement).some(([x, y]) => x < 0 || y < 0 || x >= level.width || y >= level.height);
    record('placement_rejected', { item: id, method, reason: outside ? 'bounds' : 'occupied', ...placement });
    sound('invalid', !muted); message('Not quite that spot. Try turning it or making a little room.', true);
    if (method !== 'drag') {
      board.classList.remove('gentle-shake'); void board.offsetWidth; board.classList.add('gentle-shake');
    }
    return false;
  }
  if (JSON.stringify(placements[id]) === JSON.stringify(placement)) {
    if (floatingRotation?.id === id) { floatingRotation = null; selected = null; selectionGrip = null; render(); }
    return true;
  }
  const fromRotation = floatingRotation?.id === id;
  remember(); placements[id] = { ...placement }; rotations[id] = placement.rotation;
  if (fromRotation) floatingRotation = null;
  moves++; selected = null; selectionGrip = null; hint = null;
  record('piece_placed', { item: id, method, fromRotation, ...placement, packed: Object.keys(placements).length });
  sound('place', !muted);
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) navigator.vibrate?.(8);
  status = 'A lovely little fit.'; render(); persist();
  if (isComplete(level, placements)) record('packing_ready');
  return true;
}

function turnSelected() {
  if (!selected || phase !== 'playing') return;
  const item = level.items.find(i => i.id === selected), rotation = rotationFor(selected), next = (rotation + 1) % 4;
  const grip = gripFor(selected), { height } = dimensions(rotate(item.cells, rotation));
  selectionGrip = { ...grip, x: height - grip.y, y: grip.x, rotation: next };
  let lifted = false;
  const prior = floatingRotation?.placement ?? placements[selected];
  if (prior) {
    // Keep the held point in place, then gently fit the rotated bounds at an edge.
    const { width: nextWidth, height: nextHeight } = dimensions(rotate(item.cells, next));
    const target = { x: Math.max(0, Math.min(level.width - nextWidth, Math.round(prior.x + grip.x - selectionGrip.x))),
      y: Math.max(0, Math.min(level.height - nextHeight, Math.round(prior.y + grip.y - selectionGrip.y))), rotation: next };
    if (canPlace(level, placements, selected, target)) {
      remember(); placements[selected] = target; rotations[selected] = next; floatingRotation = null;
    } else {
      // The rotated item is a transient preview; the last valid board remains saved.
      floatingRotation = { id: selected, placement: target }; lifted = true;
      const point = { x: target.x + selectionGrip.x, y: target.y + selectionGrip.y };
      cursor = { x: Math.max(0, Math.min(level.width - 1, Math.floor(point.x))), y: Math.max(0, Math.min(level.height - 1, Math.floor(point.y))) };
    }
  } else { remember(); rotations[selected] = next; }
  hint = null; moves++; record('piece_rotated', { item: selected, rotation: next, lifted, target: prior ? floatingRotation?.placement ?? placements[selected] : null });
  status = lifted ? 'Turn it freely. Drag or tap a spot to set it down.' : 'A little twist. A new possibility.';
  sound('rotate', !muted); render(); persist();
  if (document.activeElement === board) updateCursor(true);
}

function removePiece(id = selected, rotation = rotationFor(id)) {
  if (!placements[id] || phase !== 'playing') return;
  remember(); delete placements[id]; rotations[id] = rotation; moves++; selected = id; hint = null;
  if (floatingRotation?.id === id) floatingRotation = null;
  selectionGrip = null;
  record('piece_removed', { item: id, rotation }); status = 'Back on the blanket. A fresh little start.';
  render(); persist();
}

function nudge() {
  cancelFloatingRotation('hint');
  if (phase !== 'playing' || isComplete(level, placements)) return;
  const result = getHint(level, placements);
  if (!result) { message('Let’s try a fresh suitcase. Start again whenever you like.'); return; }
  remember();
  for (const id of result.release) delete placements[id];
  rotations[result.itemId] = result.placement.rotation; selected = result.itemId; hint = result;
  selectionGrip = null;
  hints++;
  const reshuffle = result.release.length ? `We’ve put ${result.release.length === 1 ? 'one item' : `${result.release.length} items`} back on the blanket. ` : '';
  status = `${reshuffle}Try ${level.items.find(i => i.id === result.itemId).name.toLowerCase()} in the dotted spot.`;
  record('hint_used', { item: result.itemId, released: result.release, target: result.placement });
  render(); persist();
}

function showPreview(id, p, isHint = false) {
  const preview = document.querySelector('#board-preview');
  if (!preview) return;
  const item = level.items.find(i => i.id === id);
  if (!item) return;
  const valid = canPlace(level, placements, id, p);
  const key = `${id}:${p.x}:${p.y}:${p.rotation}:${valid}:${isHint}`;
  if (previewKey === key) return;
  previewKey = key;
  preview.className = `board-preview ${valid ? 'valid' : 'invalid'} ${isHint ? 'hint-preview' : ''}`;
  preview.innerHTML = absoluteCells(item, p).map(([x, y]) => `<span class="preview-cell" style="left:calc(${x} * var(--cell));top:calc(${y} * var(--cell));"></span>`).join('');
}

function clearPreview() {
  const preview = document.querySelector('#board-preview'); if (preview) preview.innerHTML = '';
  previewKey = '';
  if (hint) showPreview(hint.itemId, hint.placement, true);
  else if (floatingRotation) showPreview(floatingRotation.id, floatingRotation.placement);
}

function boardPosition(clientX, clientY) {
  const rect = board.getBoundingClientRect();
  return { x: (clientX - rect.left) / (rect.width / level.width), y: (clientY - rect.top) / (rect.height / level.height) };
}

function tapPlacement(id, point) {
  if (hint?.itemId === id && absoluteCells(level.items.find(i => i.id === id), hint.placement).some(([x, y]) => x === Math.floor(point.x) && y === Math.floor(point.y))) return { ...hint.placement };
  const grip = gripFor(id);
  return { x: Math.round(point.x - grip.x), y: Math.round(point.y - grip.y), rotation: rotationFor(id) };
}

function dragVisual() {
  if (!drag?.moved) return null;
  const held = ghost.firstElementChild;
  return { rect: held.getBoundingClientRect(), html: held.innerHTML };
}

function settleVisual(visual, destination) {
  if (!visual || !destination || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const to = destination.getBoundingClientRect(), from = visual.rect;
  const floating = document.createElement('div');
  floating.className = 'settling-piece'; floating.setAttribute('aria-hidden', 'true');
  floating.innerHTML = visual.html;
  Object.assign(floating.style, { left: `${from.left}px`, top: `${from.top}px`, width: `${from.width}px`, height: `${from.height}px` });
  document.body.append(floating);
  destination.style.visibility = 'hidden';
  const animation = floating.animate([
    { transform: 'translate3d(0,0,0) scale(1)', opacity: .96 },
    { transform: `translate3d(${to.left - from.left}px,${to.top - from.top}px,0) scale(${to.width / from.width},${to.height / from.height})`, opacity: 1 },
  ], { duration: 150, easing: 'cubic-bezier(.2,.8,.2,1)', fill: 'forwards' });
  animation.finished.finally(() => { destination.style.visibility = ''; floating.remove(); });
}

function pieceDestination(id) {
  const el = document.querySelector(`[data-piece="${id}"]`);
  return el?.querySelector('.piece-visual') ?? el;
}

function dragDetails(d) {
  return { item: d.id, source: d.source, pointer: d.pointerType,
    durationMs: Math.round(performance.now() - d.startedAt), distancePx: Math.round(d.distance),
    targetChanges: d.targetChanges, blockedTargets: d.blockedTargets, trace: d.trace,
    before: d.before, rotation: d.rotation };
}

function cancelDrag(reason = 'cancel') {
  cancelAnimationFrame(dragFrame); dragFrame = 0;
  if (drag) {
    if (reason !== 'drop') {
      const visual = dragVisual();
      record('drag_cancelled', { ...dragDetails(drag), reason, moved: drag.moved });
      settleVisual(visual, pieceDestination(drag.id));
    }
    try { if (document.body.hasPointerCapture(drag.pointerId)) document.body.releasePointerCapture(drag.pointerId); } catch {}
  }
  drag = null; ghost.innerHTML = ''; ghost.classList.remove('visible'); document.body.classList.remove('dragging');
  document.querySelectorAll('.drag-source').forEach(el => el.classList.remove('drag-source'));
  document.querySelector('.blanket-panel').classList.remove('drop-ready');
  clearPreview();
}

function updateDragPosition(clientX, clientY) {
  const d = drag;
  if (!d?.moved) return;
  const { rect, unit } = d;
  d.clientX = clientX; d.clientY = clientY;
  const left = clientX - d.grabX * unit, top = clientY - d.grabY * unit;
  ghost.style.transform = `translate3d(${left}px,${top}px,0)`;
  // Grip coordinates are fractional: moving a packed item rounds its displacement,
  // rather than recentering the cell that happened to receive pointerdown.
  const target = { x: Math.round((left - rect.left) / unit), y: Math.round((top - rect.top) / unit), rotation: d.rotation };
  const nearBoard = clientX >= rect.left - unit / 2 && clientX <= rect.right + unit / 2 && clientY >= rect.top - unit / 2 && clientY <= rect.bottom + unit / 2;
  const overBlanket = clientX >= d.trayRect.left && clientX <= d.trayRect.right && clientY >= d.trayRect.top && clientY <= d.trayRect.bottom;
  const valid = canPlace(level, placements, d.id, target);
  d.target = target; d.nearBoard = nearBoard; d.overBlanket = overBlanket;
  const key = nearBoard ? `${target.x},${target.y},${target.rotation},${valid}` : overBlanket ? 'blanket' : 'outside';
  if (key !== d.targetKey) {
    d.targetKey = key; d.targetChanges++;
    if (nearBoard && !valid) d.blockedTargets++;
  }
  if (nearBoard) showPreview(d.id, target);
  else if (previewKey) clearPreview();
  ghost.classList.toggle('blocked', nearBoard && !valid);
  document.querySelector('.blanket-panel').classList.toggle('drop-ready', d.source === 'board' && overBlanket);
  const elapsed = Math.round(performance.now() - d.startedAt);
  if (elapsed - d.lastSample >= 80) {
    // Board-relative samples let us inspect hesitation without logging every frame.
    d.trace.push({ ms: elapsed, x: +((clientX - rect.left) / unit).toFixed(2), y: +((clientY - rect.top) / unit).toFixed(2), valid: nearBoard ? valid : null });
    if (d.trace.length > 60) d.trace.splice(1, 1);
    d.lastSample = elapsed;
  }
}

function liftDrag() {
  const d = drag;
  d.moved = true; hint = null;
  const { width, height } = dimensions(d.cells);
  ghost.innerHTML = `<div class="held-piece">${pieceArt(d.item, d.cells)}</div>`;
  Object.assign(ghost.style, { width: `${width * d.unit}px`, height: `${height * d.unit}px` });
  ghost.classList.add('visible'); document.body.classList.add('dragging');
  document.querySelector(`[data-piece="${d.id}"]`)?.classList.add('drag-source');
  const held = ghost.firstElementChild;
  held.style.transformOrigin = `${d.grabX * d.unit}px ${d.grabY * d.unit}px`;
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    held.animate([{ transform: `scale(${d.sourceUnit / d.unit})` }, { transform: 'scale(1)' }], { duration: 120, easing: 'cubic-bezier(.2,.8,.2,1)' });
  }
  record('drag_started', { item: d.id, source: d.source, pointer: d.pointerType, grip: { x: +d.grabX.toFixed(2), y: +d.grabY.toFixed(2), kind: d.gripKind }, before: d.before });
}

function rotateHeld() {
  const d = drag, { height } = dimensions(d.cells);
  [d.grabX, d.grabY] = [height - d.grabY, d.grabX];
  d.rotation = (d.rotation + 1) % 4; d.cells = rotate(d.item.cells, d.rotation);
  const { width, height: nextHeight } = dimensions(d.cells);
  ghost.innerHTML = `<div class="held-piece">${pieceArt(d.item, d.cells)}</div>`;
  ghost.style.width = `${width * d.unit}px`; ghost.style.height = `${nextHeight * d.unit}px`;
  record('drag_rotated', { item: d.id, rotation: d.rotation });
  updateDragPosition(d.clientX, d.clientY);
}

document.addEventListener('pointerdown', () => { suppressClickUntil = 0; }, { capture: true });
app.addEventListener('pointerdown', event => {
  const button = event.target.closest('[data-piece]');
  if (!button || phase !== 'playing' || event.button !== 0 || drag || !event.isPrimary) return;
  const item = level.items.find(i => i.id === button.dataset.piece);
  const visual = button.querySelector('.piece-visual') ?? button;
  const rect = visual.getBoundingClientRect(), cells = rotate(item.cells, rotationFor(item.id));
  const { width, height } = dimensions(cells);
  const sourceUnit = rect.width / width;
  const gx = (event.clientX - rect.left) / sourceUnit, gy = (event.clientY - rect.top) / sourceUnit;
  const ax = Math.floor(gx), ay = Math.floor(gy);
  const occupiedGrip = cells.some(([x, y]) => x === ax && y === ay);
  const grip = occupiedGrip ? { x: gx, y: gy, kind: 'click' } : { ...centerOfMass(cells), kind: 'mass' };
  const grabX = grip.x, grabY = grip.y;
  event.preventDefault();
  selectPiece(item.id, grip);
  const boardRect = board.getBoundingClientRect();
  drag = { id: item.id, item, cells, rotation: rotationFor(item.id), pointerId: event.pointerId,
    pointerType: event.pointerType, startX: event.clientX, startY: event.clientY, clientX: event.clientX, clientY: event.clientY,
    grabX, grabY, gripKind: grip.kind, sourceUnit, unit: boardRect.width / level.width, rect: boardRect,
    trayRect: document.querySelector('.blanket-panel').getBoundingClientRect(),
    moved: false, source: button.dataset.source, startedAt: performance.now(),
    before: snapshot(), distance: 0, trace: [], lastSample: -80, targetChanges: 0, blockedTargets: 0 };
  record('piece_pickup', { item: item.id, source: drag.source, pointer: event.pointerType, grip });
  try { document.body.setPointerCapture(event.pointerId); } catch { /* Synthetic pointers and older browsers may not support capture. */ }
});

document.addEventListener('pointermove', event => {
  if (!drag || event.pointerId !== drag.pointerId) return;
  drag.distance += Math.hypot(event.clientX - drag.clientX, event.clientY - drag.clientY);
  drag.clientX = event.clientX; drag.clientY = event.clientY;
  if (!drag.moved && Math.hypot(event.clientX - drag.startX, event.clientY - drag.startY) < 4) return;
  event.preventDefault();
  if (!drag.moved) liftDrag();
  if (!dragFrame) dragFrame = requestAnimationFrame(() => {
    dragFrame = 0;
    if (drag) updateDragPosition(drag.clientX, drag.clientY);
  });
}, { passive: false });

document.addEventListener('pointerup', event => {
  if (!drag || event.pointerId !== drag.pointerId) return;
  if (!drag.moved) {
    const id = drag.id, target = floatingRotation?.id === id ? { ...floatingRotation.placement } : null;
    record('piece_tap', { item: id, source: drag.source }); cancelDrag('drop');
    if (target) placePiece(id, target);
    return;
  }
  updateDragPosition(event.clientX, event.clientY);
  const ended = drag, visual = dragVisual();
  cancelDrag('drop');
  suppressClickUntil = Date.now() + 400;
  let outcome = 'outside';
  if (ended.nearBoard) outcome = placePiece(ended.id, ended.target, 'drag') ? 'placed' : 'blocked';
  else if (ended.source === 'board' && ended.overBlanket) { removePiece(ended.id, ended.rotation); outcome = 'returned'; }
  record('drag_finished', { ...dragDetails(ended), target: ended.target, outcome, after: snapshot() });
  settleVisual(visual, pieceDestination(ended.id));
});
document.addEventListener('pointercancel', () => cancelDrag('pointercancel'));
document.addEventListener('lostpointercapture', () => { if (drag) cancelDrag('capture_lost'); });
window.addEventListener('blur', () => cancelDrag('window_blur'));

board.addEventListener('pointermove', event => {
  if (drag || !selected || phase !== 'playing' || event.pointerType === 'touch') return;
  const target = tapPlacement(selected, boardPosition(event.clientX, event.clientY));
  if (floatingRotation?.id === selected) updateFloatingPosition(target);
  else showPreview(selected, target);
});
board.addEventListener('pointerleave', () => { if (!drag) clearPreview(); });

app.addEventListener('click', event => {
  if (event.detail !== 0 && Date.now() < suppressClickUntil) { event.preventDefault(); return; }
  const item = event.target.closest('[data-piece]');
  if (item) { selectPiece(item.dataset.piece); return; }
  const cell = event.target.closest('.board-cell');
  if (cell && selected) {
    const point = event.detail ? boardPosition(event.clientX, event.clientY) : { x: Number(cell.dataset.x) + .5, y: Number(cell.dataset.y) + .5 };
    placePiece(selected, tapPlacement(selected, point)); return;
  }
  const action = event.target.closest('[data-action]')?.dataset.action;
  if (action) handleAction(action, event.target.closest('[data-action]'));
  if (event.target.closest('.brand')) { event.preventDefault(); showTrips(); }
});

function handleAction(action, button) {
  record('ui_action', { action });
  if (action === 'feedback') {
    const input = document.querySelector('#playtest-feedback'), note = input.value.trim();
    if (!note) { input.focus(); return; }
    record('playtest_note', { note: note.slice(0, 1000), board: snapshot() });
    input.value = ''; document.querySelector('#feedback-status').textContent = 'Saved with your recent actions. Thank you.';
    return;
  }
  if (action === 'sound') { muted = !muted; persist(); render(); sound('place', !muted); return; }
  if (action === 'trips') { showTrips(); return; }
  if (action === 'help') { showHelp(); return; }
  if (action === 'close') { modal.close(); return; }
  if (action === 'export') { exportEvents(); return; }
  if (action === 'trip') { modal.close(); startTrip(Number(button.dataset.index)); return; }
  if (action === 'next') { modal.close(); startTrip(Math.min(levelIndex + 1, levels.length - 1)); return; }
  if (action === 'replay') { modal.close(); phase = 'playing'; restart(); return; }
  if (phase !== 'playing') return;
  if (action === 'rotate') turnSelected();
  if (action === 'remove') removePiece();
  if (action === 'hint') nudge();
  if (action === 'restart') restart();
  if (action === 'undo' && (history.length || floatingRotation)) {
    if (cancelFloatingRotation('undo')) { status = 'Back where it was. Try another way.'; render(); return; }
    ({ placements, rotations } = history.pop()); selected = null; selectionGrip = null; hint = null;
    record('undo', { board: snapshot() }); status = 'One little step back.'; render(); persist();
  }
  if (action === 'zip') finishTrip();
}

function restart() {
  floatingRotation = null;
  remember(); placements = {}; selected = null; selectionGrip = null; hint = null;
  rotations = Object.fromEntries(level.items.map(i => [i.id, i.initialRotation]));
  record('level_restart', { board: snapshot() }); moves = 0; hints = 0; activeMs = 0; clockStart = performance.now();
  status = 'A fresh blanket. A new little possibility.'; render(); persist();
}

function updateCursor(keepFloating = false) {
  board.querySelectorAll('.keyboard-cursor').forEach(el => el.classList.remove('keyboard-cursor'));
  const cell = board.querySelector(`#cell-${cursor.x}-${cursor.y}`);
  cell?.classList.add('keyboard-cursor'); board.setAttribute('aria-activedescendant', cell?.id ?? '');
  if (selected) {
    const target = tapPlacement(selected, { x: cursor.x + .5, y: cursor.y + .5 });
    if (floatingRotation?.id === selected) { if (!keepFloating) updateFloatingPosition(target); }
    else showPreview(selected, target);
  }
}

document.addEventListener('keydown', event => {
  if (modal.open || phase !== 'playing' || event.ctrlKey || event.metaKey || event.altKey) return;
  if (event.key.toLowerCase() === 'r' && selected) {
    event.preventDefault();
    if (drag) { if (!drag.moved) liftDrag(); rotateHeld(); }
    else turnSelected();
  }
  if (event.key === 'Escape') { cancelDrag('escape'); cancelFloatingRotation('escape'); record('selection_cleared', { item: selected, method: 'escape' }); selected = null; selectionGrip = null; hint = null; render(); }
  if (drag?.moved) return;
  if ((event.key === 'Delete' || event.key === 'Backspace') && placements[selected]) { event.preventDefault(); removePiece(); }
  if (document.activeElement !== board) return;
  const directions = { ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1] };
  if (directions[event.key]) {
    event.preventDefault(); const [dx, dy] = directions[event.key];
    if (floatingRotation) {
      const p = floatingRotation.placement, size = dimensions(rotate(level.items.find(i => i.id === floatingRotation.id).cells, p.rotation));
      updateFloatingPosition({ ...p, x: Math.max(0, Math.min(level.width - size.width, p.x + dx)), y: Math.max(0, Math.min(level.height - size.height, p.y + dy)) });
      updateCursor(true); return;
    }
    cursor.x = Math.max(0, Math.min(level.width - 1, cursor.x + dx)); cursor.y = Math.max(0, Math.min(level.height - 1, cursor.y + dy)); updateCursor();
  }
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    if (selected) placePiece(selected, floatingRotation?.placement ?? tapPlacement(selected, { x: cursor.x + .5, y: cursor.y + .5 }), 'keyboard');
    else {
      const item = level.items.find(i => placements[i.id] && absoluteCells(i, placements[i.id]).some(([x, y]) => x === cursor.x && y === cursor.y));
      if (item) selectPiece(item.id);
    }
  }
});
board.addEventListener('focus', () => updateCursor(true));
board.addEventListener('blur', () => board.querySelectorAll('.keyboard-cursor').forEach(el => el.classList.remove('keyboard-cursor')));

function openModal(html) {
  cancelDrag(); modal.innerHTML = html;
  if (!modal.open) modal.showModal();
}

function showTrips() {
  openModal(`<div class="modal-header"><span class="eyebrow">YOUR TRAVEL JOURNAL</span><button class="icon-button" data-action="close" aria-label="Close travel journal">${icon('close')}</button></div><h2>Five little adventures.</h2><p class="modal-intro">Pick a destination. There’s no wrong way to wander.</p><div class="trip-list">${levels.map((l, index) => `<button class="trip-card" data-action="trip" data-index="${index}"><span class="trip-thumb">${landscape(l.theme)}</span><span class="trip-card-copy"><span class="eyebrow">TRIP ${l.stamp}</span><strong>${l.title}</strong><small>${l.difficulty}</small></span><span class="trip-status ${completed.has(l.id) ? 'done' : ''}">${completed.has(l.id) ? icon('check', 22) : icon('arrow', 20)}</span></button>`).join('')}</div>`);
}

function showHelp() {
  openModal(`<div class="modal-header"><span class="eyebrow">A LITTLE GUIDANCE</span><button class="icon-button" data-action="close" aria-label="Close instructions">${icon('close')}</button></div><h2>Make a little room.</h2><p class="modal-intro">Fit every item into the suitcase. Fill every square. Then zip up and go.</p><div class="help-steps"><p><span>01</span><strong>Pick & pack</strong>Drag an item into the suitcase. Or select an item, then tap where you want its center to land. If you pick it up by its artwork, the exact point you touch becomes its anchor. With a hint, tap any dotted square.</p><p><span>02</span><strong>A little twist</strong>Select an item and tap Turn. On a keyboard, press R—even while dragging. A packed item lifts into your hand if it needs room. Drag or tap to set it down; Escape or Undo puts it back. Items rotate; they don’t flip.</p><p><span>03</span><strong>Try, rearrange, repeat</strong>Move packed items, drag them back to the blanket, or undo. A little nudge shows a spot and may return a blocking item to the blanket.</p></div><p class="keyboard-help">Keyboard: Tab to select an item or the suitcase. Use arrow keys in the suitcase, then Enter to place. Delete takes out a selected packed item.</p><div class="playtest-note"><strong>Help make packing feel better</strong><p>${import.meta.env.DEV ? 'Your actions are saved in this browser and on the Mac running this playtest. Nothing is sent to an external analytics service.' : 'Your actions stay in this browser. Export them to help improve the game.'}</p><label for="playtest-feedback">What felt awkward?</label><textarea id="playtest-feedback" maxlength="1000" rows="2" placeholder="For example: the socks landed somewhere I didn’t expect."></textarea><div class="feedback-actions"><button class="soft-button" data-action="feedback">Save a playtest note</button><button class="soft-button" data-action="export">${icon('download', 17)} Export playtest events</button></div><p id="feedback-status" role="status"></p></div>`);
}

function finishTrip() {
  if (floatingRotation || !isComplete(level, placements)) return;
  phase = 'zipping'; cancelDrag(); const firstCompletion = !completed.has(level.id); completed.add(level.id);
  record('level_complete', { firstCompletion }); persist();
  sound('complete', !muted); render();
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) navigator.vibrate?.([12, 35, 12]);
  finishTimer = setTimeout(() => {
    phase = 'finished';
    const last = levelIndex === levels.length - 1, allDone = completed.size === levels.length;
    openModal(`<div class="completion-art">${landscape(level.theme)}<span class="completion-stamp">PACKED<br><b>${level.stamp}</b><span>WITH A LITTLE LOVE</span></span></div><span class="eyebrow">${allDone ? 'FIVE TRIPS. SO MANY LITTLE THINGS.' : 'EVERYTHING IN ITS LITTLE PLACE'}</span><h2>${allDone ? 'What a lovely little journey.' : 'You’re all packed.'}</h2><p class="modal-intro">${last ? 'You’ve reached the last destination. Revisit a favorite, or find another way to make it all fit.' : level.subtitle + ' Your adventure is ready.'}</p><button class="primary-button completion-next" data-action="${last ? 'trips' : 'next'}">${last ? 'Open your travel journal' : 'The next little trip'} ${icon('arrow', 19)}</button><button class="text-button" data-action="replay">Pack this one again</button>`);
  }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 120 : 750);
}

modal.addEventListener('click', event => {
  event.stopPropagation();
  const button = event.target.closest('[data-action]');
  if (button) handleAction(button.dataset.action, button);
  if (event.target === modal) {
    const r = modal.getBoundingClientRect();
    if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) modal.close();
  }
});
modal.addEventListener('close', () => {
  if (phase === 'finished') { phase = 'playing'; render(); }
});

document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    activeMs += performance.now() - clockStart; record('session_pause', { packed: Object.keys(placements).length }); persist(); cancelDrag();
  } else { clockStart = performance.now(); record('session_resume'); }
});
window.addEventListener('pagehide', () => { record('session_end', { packed: Object.keys(placements).length }); persist(); });
window.addEventListener('resize', () => cancelDrag('resize'));

track('app_open', { version: '0.1.0', viewport: { width: innerWidth, height: innerHeight, pixelRatio: devicePixelRatio }, touch: navigator.maxTouchPoints > 0, reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches });
startTrip(levelIndex);
