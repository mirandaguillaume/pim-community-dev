import {criterionPlaceholder} from '../../../../../front/src/application/helper/CriterionHelper';

describe('CriterionHelper', () => {
  describe('criterionPlaceholder', () => {
    test('exposes a neutral placeholder shaped as a not_applicable criterion result', () => {
      expect(criterionPlaceholder).toEqual({
        rate: {value: null, rank: null},
        code: '',
        status: 'not_applicable',
        improvable_attributes: [],
      });
    });

    test('uses null for both rate value and rank so consumers know the score is unset', () => {
      expect(criterionPlaceholder.rate.value).toBeNull();
      expect(criterionPlaceholder.rate.rank).toBeNull();
    });

    test('starts with an empty improvable_attributes list', () => {
      expect(criterionPlaceholder.improvable_attributes).toHaveLength(0);
    });
  });
});
