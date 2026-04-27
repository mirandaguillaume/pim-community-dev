import {validatePimAnalyticsData} from '@akeneo-pim-community/communication-channel/src/validator/pimAnalyticsData';

test('it validates valid PIM analytics data', () => {
  const data = {pim_edition: 'community', pim_version: '6.0.0'};
  expect(validatePimAnalyticsData(data)).toEqual(data);
});

test('it validates with additional properties (schema allows them)', () => {
  const data = {pim_edition: 'enterprise', pim_version: '7.0.1', extra_key: 'value'};
  expect(validatePimAnalyticsData(data)).toEqual(data);
});

test('it throws when pim_edition is missing', () => {
  expect(() => validatePimAnalyticsData({pim_version: '6.0.0'})).toThrow('does not match the JSON schema');
});

test('it throws when pim_version is missing', () => {
  expect(() => validatePimAnalyticsData({pim_edition: 'community'})).toThrow('does not match the JSON schema');
});

test('it throws when pim_edition is not a string', () => {
  expect(() => validatePimAnalyticsData({pim_edition: 42, pim_version: '6.0.0'})).toThrow(
    'does not match the JSON schema'
  );
});
