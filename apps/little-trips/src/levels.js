import { normalize } from './engine.js';

const colors = {
  book: ['#ddb179', '#78583a'], sunglasses: ['#df9c87', '#865446'], socks: ['#a3bca1', '#55795b'],
  brush: ['#a5bdcb', '#577684'], shirt: ['#d4b5c8', '#88647a'], camera: ['#d9bd84', '#846a3b'],
  sunscreen: ['#e7b180', '#946737'], towel: ['#9dbdb3', '#507d71'], hat: ['#dbb998', '#92704b'],
  flask: ['#91a89c', '#4f6b5e'], tent: ['#a4b3c5', '#5b748f'], boots: ['#c5977f', '#845a42'],
  gift: ['#c8aec4', '#88627e'], dress: ['#df9e9b', '#965d5b'], shoes: ['#b6bb8b', '#727a46'],
  passport: ['#8baaaa', '#4d7374'], cat: ['#d5b38c', '#8c6b47'], fish: ['#a3bacb', '#59798e'],
  yarn: ['#dba4b4', '#94586f'], snack: ['#d6c593', '#8a783e'], scarf: ['#b9aec9', '#786b90'],
};

function trip(config) {
  const items = Object.entries(config.things).map(([id, [name, icon, initialRotation]]) => {
    const original = [];
    config.layout.forEach((row, y) => [...row].forEach((symbol, x) => { if (symbol === id) original.push([x, y]); }));
    const [color, ink] = colors[icon];
    return { id, name, icon, color, ink, initialRotation: initialRotation ?? 0, cells: normalize(original) };
  });
  const { layout, things, ...rest } = config;
  return { ...rest, width: layout[0].length, height: layout.length, items };
}

export const levels = [
  trip({ id: 'weekend', title: 'A weekend away', subtitle: 'A good book. A little fresh air.', destination: 'THE COUNTRYSIDE', stamp: '01', theme: 'meadow', difficulty: 'A gentle start', note: 'All the little things, in one little suitcase.',
    layout: ['AABB', 'AACC', 'DDDC'],
    things: { A: ['A good book', 'book', 0], B: ['Sunglasses', 'sunglasses', 1], C: ['Cozy socks', 'socks', 0], D: ['Toothbrush', 'brush', 1] } }),
  trip({ id: 'coast', title: 'Salt in the air', subtitle: 'Take the scenic route to the sea.', destination: 'THE SUNNY COAST', stamp: '02', theme: 'coast', difficulty: 'A little twist', note: 'Sunshine is better with your favorite hat.',
    layout: ['AABBB', 'AABCC', 'DDDCE', 'DDEEE'],
    things: { A: ['Sun lotion', 'sunscreen', 0], B: ['Beach towel', 'towel', 1], C: ['Sun hat', 'hat', 2], D: ['Linen shirt', 'shirt', 1], E: ['Little camera', 'camera', 0] } }),
  trip({ id: 'camp', title: 'Under the stars', subtitle: 'A tiny escape. A very big sky.', destination: 'THE PINE WOODS', stamp: '03', theme: 'forest', difficulty: 'Room for adventure', note: 'Leave a little room for wonder. And snacks.',
    layout: ['AAABBB', 'ACCBBD', 'ECCDDD', 'EEFFFD'],
    things: { A: ['Tent poles', 'tent', 1], B: ['Hiking boots', 'boots', 0], C: ['Wool sweater', 'shirt', 2], D: ['Water flask', 'flask', 1], E: ['Trail snacks', 'snack', 0], F: ['Warm scarf', 'scarf', 1] } }),
  trip({ id: 'wedding', title: 'Something to celebrate', subtitle: 'Dress up. Dance a little longer.', destination: 'A GARDEN WEDDING', stamp: '04', theme: 'garden', difficulty: 'An elegant squeeze', note: 'The gift is awkward. The invitation was lovely.',
    layout: ['AAABBB', 'ACCCBD', 'EECCDD', 'EFFFGD', 'EHHGGG'],
    things: { A: ['Silk scarf', 'scarf', 1], B: ['Party dress', 'dress', 2], C: ['Wrapped gift', 'gift', 1], D: ['Dancing shoes', 'shoes', 0], E: ['Photo camera', 'camera', 1], F: ['Invitation', 'passport', 1], G: ['Favorite shirt', 'shirt', 0], H: ['Sunglasses', 'sunglasses', 1] } }),
  trip({ id: 'cat', title: 'Plus one, plus whiskers', subtitle: 'Someone has packed themselves.', destination: 'A CAT-FRIENDLY CABIN', stamp: '05', theme: 'cat', difficulty: 'Quite the companion', note: 'The cat insists this is essential luggage.',
    layout: ['AAABBB', 'ACABBD', 'CCCEED', 'FCEEDD', 'FFGGGD'],
    things: { A: ['The cat, obviously', 'cat', 1], B: ['Favorite blanket', 'towel', 1], C: ['Cozy sweater', 'shirt', 0], D: ['Bag of treats', 'snack', 2], E: ['Bedtime book', 'book', 0], F: ['Ball of yarn', 'yarn', 1], G: ['Fish toy', 'fish', 1] } }),
];
