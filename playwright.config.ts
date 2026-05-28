import {defineConfig} from '@playwright/test';

// Playwright E2E configuration
export default defineConfig({
  testDir: 'tests/front/e2e',
  timeout: 600_000,
  expect: {timeout: 10_000},
  retries: 1,
  workers: 1,
  use: {
    baseURL: process.env.PIM_URL || 'http://localhost:8080',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'off',
  },
  reporter: process.env.CI ? [['github'], ['list'], ['html', {open: 'never'}]] : [['list'], ['html', {open: 'never'}]],
});
