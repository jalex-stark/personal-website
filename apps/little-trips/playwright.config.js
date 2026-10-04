import { defineConfig, devices } from '@playwright/test';

const externalURL = process.env.PLAYTEST_URL;

export default defineConfig({
  testDir: './tests/browser',
  fullyParallel: false,
  workers: 1,
  reporter: 'list',
  use: { baseURL: externalURL || 'http://127.0.0.1:4178/', trace: 'retain-on-failure', extraHTTPHeaders: { 'X-Little-Trips-Test': '1' } },
  projects: [
    { name: 'desktop', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 1000 } } },
    { name: 'iphone', use: { ...devices['iPhone 13'], defaultBrowserType: 'chromium' } },
    { name: 'safari', use: { ...devices['Desktop Safari'], viewport: { width: 1280, height: 1000 } } },
  ],
  webServer: externalURL ? undefined : { command: 'npm run dev', url: 'http://127.0.0.1:4178', reuseExistingServer: true, timeout: 30000 },
});
