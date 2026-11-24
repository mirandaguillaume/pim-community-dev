import {uuid} from './uuid';

const UUID_V4 = /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/;

afterEach(() => {
  jest.restoreAllMocks();
});

test('It generates an RFC 4122 version 4 uuid', () => {
  expect(uuid()).toMatch(UUID_V4);
});

test('It draws its randomness from the Web Crypto API, not Math.random', () => {
  const getRandomValues = jest.spyOn(crypto, 'getRandomValues');
  const mathRandom = jest.spyOn(Math, 'random');

  uuid();

  expect(getRandomValues).toHaveBeenCalledTimes(1);
  expect(mathRandom).not.toHaveBeenCalled();
});

test('It sets the version and variant bits whatever the random bytes are', () => {
  jest.spyOn(crypto, 'getRandomValues').mockImplementation(<T extends ArrayBufferView | null>(array: T): T => {
    (array as unknown as Uint8Array).fill(0xff);

    return array;
  });
  expect(uuid()).toBe('ffffffff-ffff-4fff-bfff-ffffffffffff');

  jest.spyOn(crypto, 'getRandomValues').mockImplementation(<T extends ArrayBufferView | null>(array: T): T => {
    (array as unknown as Uint8Array).fill(0x00);

    return array;
  });
  expect(uuid()).toBe('00000000-0000-4000-8000-000000000000');
});

test('It does not repeat itself', () => {
  const generated = new Set<string>();
  for (let index = 0; index < 1000; index++) {
    generated.add(uuid());
  }

  expect(generated.size).toBe(1000);
});
