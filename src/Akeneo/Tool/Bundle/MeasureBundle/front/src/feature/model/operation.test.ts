import {Operator, emptyOperation} from './operation';

describe('Operator', () => {
  it('has the expected string values', () => {
    expect(Operator.MUL).toBe('mul');
    expect(Operator.DIV).toBe('div');
    expect(Operator.ADD).toBe('add');
    expect(Operator.SUB).toBe('sub');
  });
});

describe('emptyOperation', () => {
  it('returns a MUL operation with an empty value', () => {
    expect(emptyOperation()).toStrictEqual({operator: Operator.MUL, value: ''});
  });

  it('returns a new object each call', () => {
    expect(emptyOperation()).not.toBe(emptyOperation());
  });
});
