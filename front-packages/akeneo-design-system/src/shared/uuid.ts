/**
 * RFC 4122 version 4 UUID drawn from the Web Crypto CSPRNG.
 *
 * `crypto.randomUUID()` is deliberately not used: browsers only expose it in secure contexts
 * (a PIM served over plain HTTP on a non-localhost host would get `undefined`), and the jsdom
 * shipped with the Jest environment does not implement it. `crypto.getRandomValues()` has
 * neither restriction.
 */
const uuid = (): string => {
  const bytes = new Uint8Array(16);
  crypto.getRandomValues(bytes);

  // eslint-disable-next-line no-bitwise
  bytes[6] = (bytes[6] & 0x0f) | 0x40; // version 4
  // eslint-disable-next-line no-bitwise
  bytes[8] = (bytes[8] & 0x3f) | 0x80; // RFC 4122 variant

  const hex: string[] = [];
  for (let index = 0; index < bytes.length; index++) {
    hex.push(('0' + bytes[index].toString(16)).slice(-2));
  }

  return [
    hex.slice(0, 4).join(''),
    hex.slice(4, 6).join(''),
    hex.slice(6, 8).join(''),
    hex.slice(8, 10).join(''),
    hex.slice(10, 16).join(''),
  ].join('-');
};

export {uuid};
