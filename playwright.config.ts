import {defineConfig} from '@playwright/test';

export default defineConfig({
  testDir: 'tests/front/e2e',
  timeout: 120_000,
  expect: {timeout: 10_000},
  retries: 1,
  workers: 1,
  use: {
    baseURL: process.env.PIM_URL || 'http://localhost:8080',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'off',
  },
  reporter: [['html', {open: 'never'}]],
});
