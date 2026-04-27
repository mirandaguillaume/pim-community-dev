import {
  followCriterionRecommendation,
  allowFollowingCriterionRecommendation,
} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions/followCriterionRecommendation';

describe('followCriterionRecommendation', () => {
  it('is a no-op stub that returns undefined', () => {
    expect(() => followCriterionRecommendation({} as any, null, {} as any, 'en_US')).not.toThrow();
  });
});

describe('allowFollowingCriterionRecommendation', () => {
  it('always returns false (stub implementation)', () => {
    expect(allowFollowingCriterionRecommendation({} as any, null, {} as any)).toBe(false);
  });
});
