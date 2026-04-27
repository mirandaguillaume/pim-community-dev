import {formatProductCompleteness} from './formatProductCompleteness';

const aBackendChannel = (overrides = {}) => ({
  labels: {en_US: 'E-commerce', fr_FR: 'E-commerce FR'},
  stats: {average: 75},
  locales: {
    en_US: {label: 'English (United States)', completeness: {ratio: 80}},
    fr_FR: {label: 'French (France)', completeness: {ratio: 70}},
  },
  ...overrides,
});

test('it uses catalog locale to resolve the channel label key', () => {
  const result = formatProductCompleteness([aBackendChannel()], 'en_US');
  expect(Object.keys(result)).toContain('E-commerce');
});

test('it uses the alternate catalog locale to resolve the channel label key', () => {
  const result = formatProductCompleteness([aBackendChannel()], 'fr_FR');
  expect(Object.keys(result)).toContain('E-commerce FR');
});

test('it maps channelRatio from stats.average', () => {
  const result = formatProductCompleteness([aBackendChannel({stats: {average: 55}})], 'en_US');
  expect(result['E-commerce'].channelRatio).toBe(55);
});

test('it maps localesRatios from locales using the locale label as key', () => {
  const result = formatProductCompleteness([aBackendChannel()], 'en_US');
  expect(result['E-commerce'].localesRatios).toEqual({
    'English (United States)': 80,
    'French (France)': 70,
  });
});

test('it handles multiple channels', () => {
  const channels = [
    aBackendChannel({labels: {en_US: 'E-commerce'}, stats: {average: 80}}),
    aBackendChannel({labels: {en_US: 'Print'}, stats: {average: 60}}),
  ];
  const result = formatProductCompleteness(channels, 'en_US');
  expect(Object.keys(result)).toHaveLength(2);
  expect(result['E-commerce'].channelRatio).toBe(80);
  expect(result['Print'].channelRatio).toBe(60);
});

test('it returns empty object when rawProductCompleteness is empty', () => {
  const result = formatProductCompleteness([], 'en_US');
  expect(result).toEqual({});
});

test('it handles a channel with no locales', () => {
  const channel = aBackendChannel({locales: {}});
  const result = formatProductCompleteness([channel], 'en_US');
  expect(result['E-commerce'].localesRatios).toEqual({});
});
