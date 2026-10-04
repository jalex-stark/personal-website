import { dimensions } from './engine.js';

const drawings = {
  book: '<rect x="19" y="10" width="62" height="80" rx="6" fill="currentColor" fill-opacity=".10"/><path d="M30 11v78M40 28h29M40 36h21M40 72h22"/><path d="M19 81q0 9 9 9h53"/>',
  sunglasses: '<path d="m9 33 5-6h72l5 6M46 43q4-7 8 0M8 39l8 27M92 39l-8 27"/><path d="M15 36h29v19q-1 15-15 13Q16 67 15 54zM56 36h29v18q-1 13-14 14-14 1-15-13z" fill="currentColor" fill-opacity=".18"/>',
  socks: '<path d="M18 12h28v42l-16 25q-8 13-20 4-9-7 0-19l8-13zM61 12h27v39l-15 27q-8 13-20 5-10-7-1-20l9-14z" fill="currentColor" fill-opacity=".12"/><path d="M18 24h28M61 24h27M15 61l17 12M57 59l16 12"/>',
  brush: '<path d="M42 39h16v44q0 10-8 10t-8-10z" fill="currentColor" fill-opacity=".12"/><rect x="36" y="8" width="28" height="32" rx="7"/><path d="M42 15v17M50 15v17M58 15v17M47 69h6"/>',
  shirt: '<path d="m33 15 17 7 17-7 23 20-13 19-10-7v41H33V47l-10 7-13-19z" fill="currentColor" fill-opacity=".12"/><path d="M33 15q5 18 17 18t17-18M41 49h18M42 57h16M34 79h32"/>',
  camera: '<path d="M16 30h19l6-13h20l6 13h17q7 0 7 7v42q0 7-7 7H16q-7 0-7-7V37q0-7 7-7z" fill="currentColor" fill-opacity=".12"/><circle cx="51" cy="58" r="21"/><circle cx="51" cy="58" r="12"/><path d="M18 39h9M74 39h7"/>',
  sunscreen: '<path d="M32 26h36l5 57q0 8-9 8H36q-9 0-9-8z" fill="currentColor" fill-opacity=".12"/><rect x="34" y="8" width="32" height="18" rx="4"/><circle cx="50" cy="58" r="11"/><path d="M50 40v-4M50 80v-4M32 58h-4M72 58h-4M36 44l-3-3M67 75l-3-3M64 44l3-3M33 75l3-3"/>',
  towel: '<rect x="19" y="12" width="62" height="73" rx="5" fill="currentColor" fill-opacity=".12"/><path d="M20 27h60M20 35h60M20 62h60M20 70h60M27 85v7M39 85v7M51 85v7M63 85v7M75 85v7"/>',
  hat: '<path d="M26 56q1-40 24-40t24 40" fill="currentColor" fill-opacity=".12"/><ellipse cx="50" cy="64" rx="42" ry="17" fill="currentColor" fill-opacity=".10"/><path d="M26 49q24 9 48 0M25 57q25 9 50 0"/>',
  flask: '<rect x="30" y="29" width="40" height="62" rx="14" fill="currentColor" fill-opacity=".14"/><path d="M37 29V17h26v12M40 17V8h20v9M42 48v25M50 76h9"/>',
  tent: '<path d="m9 80 41-67 41 67z" fill="currentColor" fill-opacity=".13"/><path d="M50 13v67M31 80l19-37 19 37M7 84h86M15 66l-7 6M86 66l7 6"/>',
  boots: '<path d="M19 15h29v47l20 9q17 5 17 18H15V69z" fill="currentColor" fill-opacity=".15"/><path d="M15 82h68M24 26h18M24 36h18M24 46h18M45 62l-6 8M54 67l-5 7M64 71l-4 7"/>',
  gift: '<rect x="15" y="35" width="70" height="55" rx="4" fill="currentColor" fill-opacity=".12"/><path d="M9 35h82v13H9zM44 35v55h12V35M50 34C11 34 21 2 38 16l12 18c40 0 29-31 12-18z"/>',
  dress: '<path d="M35 12h30l-5 26 24 50H16l24-50z" fill="currentColor" fill-opacity=".12"/><path d="M35 12q15 19 30 0M40 38h20M33 61l-4 16M48 57v20M63 61l7 16"/>',
  shoes: '<path d="M12 51q20 11 31-24 5-12 15-5l10 11q-12 14-13 29l28 13q10 7 4 14H14q-10-2-10-13z" fill="currentColor" fill-opacity=".12"/><path d="M14 81h69M56 56l10 5M43 64l10 6"/>',
  passport: '<rect x="20" y="9" width="60" height="82" rx="7" fill="currentColor" fill-opacity=".13"/><circle cx="50" cy="47" r="17"/><ellipse cx="50" cy="47" rx="7" ry="17"/><path d="M33 47h34M38 36q12 7 24 0M38 58q12-7 24 0M36 75h28"/>',
  cat: '<path d="m27 36-5-23 22 13q8-2 16 0l20-13-4 25q10 8 8 25-2 25-32 25S17 74 17 61q0-17 10-25z" fill="currentColor" fill-opacity=".12"/><path d="M35 52h1M66 52h1M47 60l5 5 5-5M52 65v7M43 69q9 9 18 0M12 57l20 5M12 69l20-3M72 62l18-5M72 68l18 3"/><path d="M27 29l-1-8 8 9M68 30l7-9-1 10"/>',
  fish: '<path d="M15 51Q48 4 78 44l16-17v47L78 57Q45 96 15 51z" fill="currentColor" fill-opacity=".13"/><circle cx="31" cy="45" r="2"/><path d="M44 28q-10 22 0 44M54 27l8-11M54 75l8 10"/>',
  yarn: '<circle cx="50" cy="46" r="32" fill="currentColor" fill-opacity=".12"/><path d="M23 29q37 4 56 33M30 21q36 3 52 27M19 44q23-2 49 30M23 60q17-3 27 18M69 22Q35 28 28 68M80 35Q50 37 44 76M65 75q-5 15 5 14t19-2"/>',
  snack: '<path d="M23 12h54l-5 18 5 58H23l5-58z" fill="currentColor" fill-opacity=".12"/><path d="M23 19h54M25 80h50M34 42h32v25H34zM41 54l8-7 11 12M40 12v7M51 12v7M63 12v7"/>',
  scarf: '<path d="M28 11h24v46h25v27H28z" fill="currentColor" fill-opacity=".12"/><path d="M29 24h22M29 34h22M29 44h22M37 58v25M48 59v24M59 59v24M70 59v24M32 84v9M42 84v9M53 84v9M64 84v9M74 84v9"/>',
};

export function icon(name, size = 24) {
  const paths = {
    suitcase: '<rect x="3" y="7" width="18" height="14" rx="4"/><path d="M8 7V3h8v4M8 8v12M16 8v12"/>',
    rotate: '<path d="M20 8a8 8 0 1 0 1 7M20 3v5h-5"/>',
    undo: '<path d="m8 5-5 5 5 5M3 10h11a6 6 0 0 1 0 12"/>',
    hint: '<path d="M9 18h6M10 21h4M8 14a6 6 0 1 1 8 0c-1 1-1 2-1 2H9s0-1-1-2zM12 1v1M3 5l1 1M21 5l-1 1"/>',
    sound: '<path d="m3 9 5 0 5-5v16l-5-5H3zM17 8a6 6 0 0 1 0 8M20 5a10 10 0 0 1 0 14"/>',
    mute: '<path d="m3 9 5 0 5-5v16l-5-5H3zM17 9l5 6M22 9l-5 6"/>',
    help: '<circle cx="12" cy="12" r="9"/><path d="M9 8a3 3 0 0 1 6 1c0 2-3 2-3 4M12 17v.1"/>',
    close: '<path d="m6 6 12 12M18 6 6 18"/>',
    arrow: '<path d="M4 12h16m-6-6 6 6-6 6"/>',
    check: '<path d="m5 12 4 4L19 6"/>',
    restart: '<path d="M4 8a8 8 0 1 1-1 7M4 3v5h5"/>',
    sparkle: '<path d="m12 2 3 7 7 3-7 3-3 7-3-7-7-3 7-3z"/>',
    plane: '<path d="m3 10 18-7-7 18-3-8-8-3zM11 13l10-10"/>',
    download: '<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>',
  };
  return `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[name] ?? paths.sparkle}</svg>`;
}

function outline(cells) {
  const set = new Set(cells.map(p => p.join(',')));
  const edges = [];
  for (const [x, y] of cells) {
    if (!set.has(`${x},${y - 1}`)) edges.push([[x, y], [x + 1, y]]);
    if (!set.has(`${x + 1},${y}`)) edges.push([[x + 1, y], [x + 1, y + 1]]);
    if (!set.has(`${x},${y + 1}`)) edges.push([[x + 1, y + 1], [x, y + 1]]);
    if (!set.has(`${x - 1},${y}`)) edges.push([[x, y + 1], [x, y]]);
  }
  const points = [edges[0][0]];
  let end = edges[0][1];
  while (end.join(',') !== points[0].join(',')) {
    points.push(end);
    const next = edges.find(([start]) => start.join(',') === end.join(','));
    if (!next || points.length > edges.length) break;
    end = next[1];
  }
  const r = .105;
  const lerp = (a, b, t) => a.map((v, i) => (v + (b[i] - v) * t) * 100);
  let path = '';
  points.forEach((p, i) => {
    const prev = points[(i + points.length - 1) % points.length], next = points[(i + 1) % points.length];
    const a = lerp(p, prev, r), b = lerp(p, next, r);
    path += `${i ? 'L' : 'M'}${a.join(' ')}Q${p.map(v => v * 100).join(' ')} ${b.join(' ')}`;
  });
  return `${path}Z`;
}

export function pieceArt(item, cells) {
  const { width, height } = dimensions(cells);
  const w = width * 100, h = height * 100;
  const path = outline(cells);
  // Put the illustration inside the largest filled rectangle, never in a hole.
  const set = new Set(cells.map(c => c.join(',')));
  let spot = { x: cells[0][0], y: cells[0][1], w: 1, h: 1, area: 1 };
  for (const [x, y] of cells) for (let rw = 1; rw <= width - x; rw++) for (let rh = 1; rh <= height - y; rh++) {
    let filled = true;
    for (let a = x; a < x + rw; a++) for (let b = y; b < y + rh; b++) if (!set.has(`${a},${b}`)) filled = false;
    if (filled && rw * rh > spot.area) spot = { x, y, w: rw, h: rh, area: rw * rh };
  }
  const size = Math.min(spot.w * 100 - 22, spot.h * 100 - 22, 118);
  const ix = (spot.x + spot.w / 2) * 100 - size / 2, iy = (spot.y + spot.h / 2) * 100 - size / 2;
  return `<svg class="object-art" viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="${path}" fill="${item.color}" stroke="${item.ink}" stroke-width="3"/>
    <path d="${path}" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="9"/>
    <g transform="translate(${ix} ${iy}) scale(${size / 100})" color="${item.ink}" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">${drawings[item.icon]}</g>
    ${cells.map(([x, y]) => `<circle cx="${x * 100 + 14}" cy="${y * 100 + 15}" r="1.6" fill="${item.ink}" opacity=".23"/>`).join('')}
  </svg>`;
}

export function landscape(theme) {
  const sky = theme === 'coast' ? '#e2ece9' : theme === 'garden' ? '#efe2df' : theme === 'cat' ? '#efe2cb' : '#e2e7dc';
  const trees = '<path d="m268 44-20 41h40zM306 65l-22 43h44zM244 80l-18 35h36z" fill="#66836a"/><path d="M268 78v27M306 105v16M244 109v15" stroke="#536e57" stroke-width="3"/>';
  let content = '';
  if (theme === 'coast') content = '<path d="M0 94q85-17 165 2t195-2v86H0z" fill="#92b9bc"/><path d="M0 137q90-30 165-6t195 6v43H0z" fill="#e6c897"/><path d="M60 115q30-7 56 0M210 110q25-7 50 0" stroke="#e7f0e9" stroke-width="3" fill="none"/><path d="M277 95V48m0 0-19 30h19z" stroke="#617f85" fill="#f9f0d7" stroke-width="3"/>';
  else if (theme === 'forest') content = '<path d="m0 126 93-99 68 73 56-65 143 100v45H0z" fill="#a6b8ad"/><path d="m66 57 27-30 29 31-17-7-12 7-11-8z" fill="#f7f3e8"/><path d="M0 144q90-47 180-9t180-7v52H0z" fill="#7e9a7b"/>' + trees + '<path d="m129 159 33-45 35 45z" fill="#e4b278"/><path d="m155 159 7-27 12 27z" fill="#8c7456"/>';
  else if (theme === 'garden') content = '<path d="M0 126q90-36 180-6t180 0v60H0z" fill="#a8b69a"/><path d="M0 156q90-28 180-4t180 0v28H0z" fill="#7e9778"/><path d="M155 142V89a27 27 0 0 1 54 0v53" stroke="#f7f1df" stroke-width="9" fill="none"/><path d="M68 131v-33M57 110l11 12 11-12M285 135v-28M274 118l11 9 11-9" stroke="#64815f" stroke-width="3" fill="none"/><g fill="#dba7a2"><circle cx="68" cy="99" r="8"/><circle cx="285" cy="107" r="7"/><circle cx="156" cy="91" r="7"/><circle cx="164" cy="74" r="7"/><circle cx="188" cy="64" r="6"/></g>';
  else if (theme === 'cat') content = '<path d="M0 130q90-40 180-10t180 0v60H0z" fill="#a3b393"/>' + trees + '<path d="M89 103h87v52H89z" fill="#c59474"/><path d="m77 105 56-49 56 49z" fill="#816b59"/><path d="M121 155v-35h24v35M100 116h13v14h-13z" fill="#ead0a1"/><path d="M209 146q-4-17 4-23l-1-10 10 7 10-7-1 11q8 14 0 22z" fill="#c69472"/><path d="M230 143q22 6 15-11" stroke="#c69472" stroke-width="6" fill="none"/>';
  else content = '<path d="M0 113q77-32 175 4t185-1v64H0z" fill="#adbc9c"/><path d="M0 145q120-44 235-12t125 13v34H0z" fill="#819c76"/>' + trees + '<path d="M75 126h67V91H75z" fill="#e4c59b"/><path d="m66 94 43-31 42 31z" fill="#b7826c"/><path d="M99 126v-23h14v23M82 103h10v10H82z" fill="#77918b"/>';
  return `<svg viewBox="0 0 360 180" aria-hidden="true"><rect width="360" height="180" rx="14" fill="${sky}"/><circle cx="64" cy="43" r="19" fill="#f2d491"/><path d="M141 38q3-15 17-11 9-13 18-1 15-1 16 12z" fill="#f8f6eb"/>${content}</svg>`;
}
