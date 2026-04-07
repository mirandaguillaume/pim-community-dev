import {isSuccess} from '../../../../../front/src/application/helper/RateHelper';
import {MAX_RATE, Rate} from '../../../../../front/src/domain';

describe('RateHelper', () => {
  describe('isSuccess', () => {
    test('returns true when rate.value equals MAX_RATE', () => {
      const rate: Rate = {value: MAX_RATE, rank: 1};

      expect(isSuccess(rate)).toBe(true);
    });

    test('returns false when rate.value is below MAX_RATE', () => {
      const rate: Rate = {value: MAX_RATE - 1, rank: 2};

      expect(isSuccess(rate)).toBe(false);
    });

    test('returns false when rate.value is null', () => {
      const rate: Rate = {value: null, rank: null};

      expect(isSuccess(rate)).toBe(false);
    });

    test('returns false (falsy passthrough) when the rate itself is null', () => {
      // @ts-expect-error: testing the defensive `rate &&` short-circuit
      expect(isSuccess(null)).toBeFalsy();
    });

    test('returns false (falsy passthrough) when the rate itself is undefined', () => {
      // @ts-expect-error: testing the defensive `rate &&` short-circuit
      expect(isSuccess(undefined)).toBeFalsy();
    });
  });
});
