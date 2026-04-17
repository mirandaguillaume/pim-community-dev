import {unformatNumber, formatNumber} from './number';

describe('tools/number.ts', () => {
  describe('unformatNumber', () => {
    it('replaces a custom decimal separator with the standard dot', () => {
      expect(unformatNumber(',')('3,14')).toEqual('3.14');
    });

    it('falls back to dot when separator is empty', () => {
      expect(unformatNumber('')('3.14')).toEqual('3.14');
    });

    it('strips whitespace from the number', () => {
      expect(unformatNumber('.')('3 . 14')).toEqual('3.14');
      expect(unformatNumber(',')('1 000,50')).toEqual('1000.50');
    });

    it('strips letter suffixes (units) from the number', () => {
      expect(unformatNumber('.')('3.14kg')).toEqual('3.14');
      expect(unformatNumber('.')('100mL')).toEqual('100');
    });

    it('does not replace separator when it is already the standard dot', () => {
      expect(unformatNumber('.')('3.14')).toEqual('3.14');
    });

    it('handles multiple occurrences of the separator', () => {
      expect(unformatNumber(',')('1,000,50')).toEqual('1.000.50');
    });
  });

  describe('formatNumber', () => {
    it('replaces dot with the given decimal separator', () => {
      expect(formatNumber(',')('3.14')).toEqual('3,14');
    });

    it('falls back to dot when separator is empty', () => {
      expect(formatNumber('')('3.14')).toEqual('3.14');
    });

    it('handles multiple dots', () => {
      expect(formatNumber(',')('1.000.50')).toEqual('1,000,50');
    });

    it('returns the number unchanged when separator is dot', () => {
      expect(formatNumber('.')('3.14')).toEqual('3.14');
    });
  });
});
