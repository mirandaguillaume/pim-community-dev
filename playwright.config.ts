import {defineConfig} from '@playwright/test';

// Shard the suite from an env var so CI can fan it across parallel jobs. `workers: 1`
// keeps tests sequential WITHIN a job (they share one PIM stack, so parallel workers
// would collide on DB/ES state); sharding instead gives each shard its OWN isolated
// stack. Format: PW_SHARD="1/4". Unset (local runs / non-sharded CI) → whole suite.
const shardEnv = process.env.PW_SHARD;
const shard = shardEnv ? {current: Number(shardEnv.split('/')[0]), total: Number(shardEnv.split('/')[1])} : null;

// Playwright E2E configuration
export default defineConfig({
  testDir: 'tests/front/e2e',
  timeout: 600_000,
  expect: {timeout: 10_000},
  retries: 1,
  workers: 1,
  shard,
  use: {
    baseURL: process.env.PIM_URL || 'http://localhost:8080',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'off',
  },
  reporter: process.env.CI
    ? [['github'], ['list'], ['html', {open: 'never'}], ['json', {outputFile: 'playwright-report/results.json'}]]
    : [['list'], ['html', {open: 'never'}]],
});
