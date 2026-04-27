import {validateHasNewAnnouncements} from '@akeneo-pim-community/communication-channel/src/validator/hasNewAnnouncements';

test('it validates a valid hasNewAnnouncements object', () => {
  expect(validateHasNewAnnouncements({status: true})).toEqual({status: true});
  expect(validateHasNewAnnouncements({status: false})).toEqual({status: false});
});

test('it validates with additional properties (schema allows them)', () => {
  const data = {status: true, extra: 'allowed'};
  expect(validateHasNewAnnouncements(data)).toEqual(data);
});

test('it throws when status is missing', () => {
  expect(() => validateHasNewAnnouncements({})).toThrow('does not match the JSON schema');
});

test('it throws when status is not a boolean', () => {
  expect(() => validateHasNewAnnouncements({status: 'yes'})).toThrow('does not match the JSON schema');
});
