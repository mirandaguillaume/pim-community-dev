import {validateAnnouncement} from '@akeneo-pim-community/communication-channel/src/validator/announcement';

const aValidAnnouncement = () => ({
  id: 'announcement-1',
  title: 'New feature',
  description: 'This is a new feature',
  img: null,
  altImg: null,
  link: 'https://example.com',
  tags: ['new', 'feature'],
  startDate: '2024-01-01',
});

test('it validates a valid announcement', () => {
  const data = aValidAnnouncement();
  expect(validateAnnouncement(data)).toEqual(data);
});

test('it validates an announcement with image', () => {
  const data = {...aValidAnnouncement(), img: 'https://example.com/img.png', altImg: 'alt text'};
  expect(validateAnnouncement(data)).toEqual(data);
});

test('it throws when a required field is missing', () => {
  const {id: _id, ...missingId} = aValidAnnouncement();
  expect(() => validateAnnouncement(missingId)).toThrow('does not match the JSON schema');
});

test('it throws when tags is not an array', () => {
  const data = {...aValidAnnouncement(), tags: 'not-an-array'};
  expect(() => validateAnnouncement(data)).toThrow('does not match the JSON schema');
});

test('it throws when an additional property is present', () => {
  const data = {...aValidAnnouncement(), unexpectedField: 'value'};
  expect(() => validateAnnouncement(data)).toThrow('does not match the JSON schema');
});
