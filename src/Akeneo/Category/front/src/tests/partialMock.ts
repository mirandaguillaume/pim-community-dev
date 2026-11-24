/**
 * A test double that defines only what the code under test reads. Every property given is checked
 * against the real type; leaving the others out is what the cast allows, and all it allows.
 */
const partialMock = <T>(value: Partial<T>): T => value as T;

export {partialMock};
