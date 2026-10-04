let context;
export function sound(type, enabled) {
  if (!enabled) return;
  try {
    context ??= new (window.AudioContext || window.webkitAudioContext)();
    if (context.state === 'suspended') void context.resume().catch(() => {});
    const notes = type === 'complete' ? [523.25, 659.25, 783.99, 1046.5] : type === 'rotate' ? [392, 440] : type === 'invalid' ? [185] : [440, 554.37];
    notes.forEach((frequency, index) => {
      const o = context.createOscillator(), g = context.createGain(), t = context.currentTime + index * .075;
      o.type = 'sine'; o.frequency.value = frequency;
      g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(type === 'invalid' ? .025 : .045, t + .012);
      g.gain.exponentialRampToValueAtTime(.001, t + .2);
      o.connect(g); g.connect(context.destination); o.start(t); o.stop(t + .22);
    });
  } catch { /* Sound is optional, including in browsers without Web Audio. */ }
}
