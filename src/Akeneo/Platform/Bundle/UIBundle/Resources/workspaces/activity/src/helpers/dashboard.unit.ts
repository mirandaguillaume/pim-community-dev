import {generateRandomNumber} from './dashboard';

afterEach(() => jest.restoreAllMocks());

test('it returns 0 when Math.random returns 0', () => {
  jest.spyOn(Math, 'random').mockReturnValue(0);
  expect(generateRandomNumber(10)).toBe(0);
});

test('it returns the rounded product of max and the random value', () => {
  jest.spyOn(Math, 'random').mockReturnValue(0.5);
  expect(generateRandomNumber(10)).toBe(5);
});

test('it can return max when Math.random returns 1', () => {
  jest.spyOn(Math, 'random').mockReturnValue(1);
  expect(generateRandomNumber(10)).toBe(10);
});
