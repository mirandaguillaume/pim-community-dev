import {Direction} from './direction';

describe('Direction', () => {
  it('has an Ascending value of "ascending"', () => {
    expect(Direction.Ascending).toBe('ascending');
  });

  it('has a Descending value of "descending"', () => {
    expect(Direction.Descending).toBe('descending');
  });
});
