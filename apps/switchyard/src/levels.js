import { direction, key, ports } from './engine.js';

const colors = ['#c48a3d', '#5285a2', '#ac6579'];
const labels = ['Amber', 'Blue', 'Rose'];

function level(id, title, note, paths, obstacles = [], delays = []) {
  const cells = new Map();
  const trains = paths.map((path, i) => {
    const points = path.map(([x, y]) => ({ x, y }));
    for (let j = 1; j < points.length - 1; j++) {
      const p = points[j], k = key(p.x, p.y), c = cells.get(k) || { x: p.x, y: p.y, directions: new Set() };
      c.directions.add(direction(p, points[j - 1])); c.directions.add(direction(p, points[j + 1])); cells.set(k, c);
    }
    return { id: `train-${i}`, label: labels[i], color: colors[i], source: points[0], target: points.at(-1), direction: direction(points[0], points[1]), delay: delays[i] || 0 };
  });
  const tiles = [], solution = {};
  for (const c of cells.values()) {
    const ds = [...c.directions].sort(), type = ds.length === 4 ? 'cross' : (ds[1] - ds[0] === 2 ? 'straight' : 'curve');
    if (ds.length !== 2 && ds.length !== 4) throw new Error('Unsupported authored junction.');
    const r = [0, 1, 2, 3].find(r => ports(type, r).sort().join() === ds.join());
    const tile = { id: `track-${tiles.length}`, type };
    tiles.push(tile); solution[tile.id] = { x: c.x, y: c.y, r };
  }
  return { id, title, note, size: 5, obstacles, trains, tiles, solution };
}

export const levels = [
  level('first-departure', 'First departure', 'A straight line, a fresh start. Join the amber engine to its matching platform.', [
    [[-1, 2], [0, 2], [1, 2], [2, 2], [3, 2], [4, 2], [5, 2]],
  ]),
  level('around-the-pond', 'Around the pond', 'The scenic route has a couple of turns. Leave the water undisturbed.', [
    [[-1, 3], [0, 3], [1, 3], [1, 2], [1, 1], [2, 1], [3, 1], [4, 1], [5, 1]],
  ], [[2, 3], [3, 3], [2, 2]]),
  level('passing-through', 'Passing through', 'Two trains leave together. A crossing lets both pass straight through—but only one at a time.', [
    [[-1, 2], [0, 2], [0, 1], [1, 1], [2, 1], [3, 1], [3, 2], [4, 2], [5, 2]],
    [[2, -1], [2, 0], [2, 1], [2, 2], [2, 3], [2, 4], [2, 5]],
  ]),
  level('morning-connections', 'Morning connections', 'Blue departs one beat after amber. Let the timetable do a little work for you.', [
    [[-1, 1], [0, 1], [1, 1], [2, 1], [3, 1], [3, 2], [3, 3], [3, 4], [3, 5]],
    [[1, -1], [1, 0], [1, 1], [1, 2], [1, 3], [2, 3], [3, 3], [4, 3], [5, 3]],
  ], [[2, 2], [2, 4], [4, 1]], [0, 1]),
  level('last-trains-home', 'Last trains home', 'Three little engines, two crossings. Find a quiet way home for everyone.', [
    [[-1, 1], [0, 1], [1, 1], [2, 1], [3, 1], [4, 1], [5, 1]],
    [[-1, 4], [0, 4], [1, 4], [1, 3], [2, 3], [3, 3], [4, 3], [4, 4], [4, 5]],
    [[2, -1], [2, 0], [2, 1], [2, 2], [2, 3], [2, 4], [2, 5]],
  ], [[3, 4], [3, 2], [4, 2]], [0, 1, 0]),
];
