import {formatCurrentCompleteness} from './formatCurrentCompleteness';

const aBackendLocale = (overrides = {}) => ({
  completeness: {missing: 2, ratio: 80},
  missing: [
    {code: 'name', labels: {en_US: 'Name', fr_FR: 'Nom'}},
    {code: 'description', labels: {en_US: 'Description', fr_FR: 'Description'}},
  ],
  label: 'English (United States)',
  ...overrides,
});

const aBackendCompleteness = (overrides = {}) => ({
  stats: {average: 80},
  locales: {
    en_US: aBackendLocale(),
  },
  ...overrides,
});

test('it maps channelRatio from stats.average', () => {
  const result = formatCurrentCompleteness(aBackendCompleteness({stats: {average: 65}}), 'en_US');
  expect(result.channelRatio).toBe(65);
});

test('it maps locale label, ratio and missingCount', () => {
  const result = formatCurrentCompleteness(aBackendCompleteness(), 'en_US');
  const locale = result.localesCompleteness['en_US'];
  expect(locale.label).toBe('English (United States)');
  expect(locale.ratio).toBe(80);
  expect(locale.missingCount).toBe(2);
});

test('it maps missing attributes using the catalog locale for the label', () => {
  const result = formatCurrentCompleteness(aBackendCompleteness(), 'fr_FR');
  const locale = result.localesCompleteness['en_US'];
  expect(locale.missingAttributes).toEqual([
    {code: 'name', label: 'Nom'},
    {code: 'description', label: 'Description'},
  ]);
});

test('it maps missing attribute label to undefined when catalog locale is not in labels', () => {
  const result = formatCurrentCompleteness(aBackendCompleteness(), 'de_DE');
  const locale = result.localesCompleteness['en_US'];
  expect(locale.missingAttributes[0].label).toBeUndefined();
});

test('it handles multiple locales', () => {
  const backend = aBackendCompleteness({
    stats: {average: 70},
    locales: {
      en_US: aBackendLocale({label: 'English', completeness: {missing: 1, ratio: 90}}),
      fr_FR: aBackendLocale({label: 'French', completeness: {missing: 3, ratio: 60}}),
    },
  });
  const result = formatCurrentCompleteness(backend, 'en_US');
  expect(Object.keys(result.localesCompleteness)).toHaveLength(2);
  expect(result.localesCompleteness['en_US'].ratio).toBe(90);
  expect(result.localesCompleteness['fr_FR'].ratio).toBe(60);
});

test('it returns empty localesCompleteness when locales is empty', () => {
  const result = formatCurrentCompleteness({stats: {average: 0}, locales: {}}, 'en_US');
  expect(result.localesCompleteness).toEqual({});
  expect(result.channelRatio).toBe(0);
});

test('it handles a locale with no missing attributes', () => {
  const backend = aBackendCompleteness({
    locales: {
      en_US: aBackendLocale({completeness: {missing: 0, ratio: 100}, missing: []}),
    },
  });
  const result = formatCurrentCompleteness(backend, 'en_US');
  expect(result.localesCompleteness['en_US'].missingAttributes).toEqual([]);
  expect(result.localesCompleteness['en_US'].missingCount).toBe(0);
});
