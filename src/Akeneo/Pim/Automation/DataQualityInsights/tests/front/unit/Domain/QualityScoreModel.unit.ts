import {allScoreValues} from '../../../../front/src/domain/QualityScoreModel';

describe('QualityScoreModel', () => {
  it('allScoreValues contains exactly the 5 letter grades A through E', () => {
    expect(allScoreValues).toEqual(['A', 'B', 'C', 'D', 'E']);
  });

  it('allScoreValues has length 5', () => {
    expect(allScoreValues).toHaveLength(5);
  });

  it('A is the best score (first entry)', () => {
    expect(allScoreValues[0]).toBe('A');
  });

  it('E is the worst score (last entry)', () => {
    expect(allScoreValues[4]).toBe('E');
  });
});
