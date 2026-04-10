import {ServerError} from './ServerError';
import {Unauthorized} from './Unauthorized';
import {IdentifierGeneratorNotFound} from './IdentifierGeneratorNotFound';
import {InvalidIdentifierGenerator} from './InvalidIdentifierGenerator';
import {Violation} from '../validators/Violation';

describe('ServerError', () => {
  it('is an instance of Error', () => {
    expect(new ServerError()).toBeInstanceOf(Error);
  });
  it('is an instance of ServerError', () => {
    expect(new ServerError('msg')).toBeInstanceOf(ServerError);
  });
  it('carries the provided message', () => {
    expect(new ServerError('server failed').message).toBe('server failed');
  });
});

describe('Unauthorized', () => {
  it('is an instance of Error', () => {
    expect(new Unauthorized()).toBeInstanceOf(Error);
  });
  it('is an instance of Unauthorized', () => {
    expect(new Unauthorized()).toBeInstanceOf(Unauthorized);
  });
});

describe('IdentifierGeneratorNotFound', () => {
  it('is an instance of Error', () => {
    expect(new IdentifierGeneratorNotFound()).toBeInstanceOf(Error);
  });
  it('is an instance of IdentifierGeneratorNotFound', () => {
    expect(new IdentifierGeneratorNotFound()).toBeInstanceOf(IdentifierGeneratorNotFound);
  });
});

describe('InvalidIdentifierGenerator', () => {
  const violations: Violation[] = [
    {path: 'code', message: 'This value is required.'},
    {message: 'Structure cannot be empty.'},
  ];

  it('is an instance of Error', () => {
    expect(new InvalidIdentifierGenerator(violations)).toBeInstanceOf(Error);
  });

  it('is an instance of InvalidIdentifierGenerator', () => {
    expect(new InvalidIdentifierGenerator(violations)).toBeInstanceOf(InvalidIdentifierGenerator);
  });

  it("has the message 'Invalid identifier generator'", () => {
    expect(new InvalidIdentifierGenerator(violations).message).toBe('Invalid identifier generator');
  });

  it('stores violations on the instance', () => {
    const error = new InvalidIdentifierGenerator(violations);
    expect(error.violations).toStrictEqual(violations);
  });

  it('stores an empty violations array when given none', () => {
    const error = new InvalidIdentifierGenerator([]);
    expect(error.violations).toStrictEqual([]);
  });

  it('preserves the path field of each violation', () => {
    const error = new InvalidIdentifierGenerator(violations);
    expect(error.violations[0].path).toBe('code');
    expect(error.violations[1].path).toBeUndefined();
  });
});
