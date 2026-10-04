import { getStore } from '@netlify/blobs';
import { createHandler } from '../lib/action-log.mjs';
import { readTokenHash } from '../lib/read-token-hash.mjs';

export default createHandler(() => getStore({ name: 'game-action-logs-v1', consistency: 'strong' }), readTokenHash);
export const config = {
  path: '/api/game-actions',
  rateLimit: { action: 'rate_limit', aggregateBy: 'ip', windowSize: 60, windowLimit: 120 },
};
