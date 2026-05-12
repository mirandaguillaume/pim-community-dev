import {
  MAX_RATE,
  RANK_1,
  RANK_2,
  RANK_3,
  RANK_4,
  RANK_5,
  Ranks,
  RANK_1_COLOR,
  RANK_2_COLOR,
  RANK_3_COLOR,
  RANK_4_COLOR,
  RANK_5_COLOR,
  NO_RATE_COLOR,
} from '../../../../front/src/domain/Rate.interface';

describe('Rate domain constants', () => {
  it('MAX_RATE is 100', () => {
    expect(MAX_RATE).toBe(100);
  });

  it('rank labels follow the A–E grading scale', () => {
    expect(RANK_1).toBe('A');
    expect(RANK_2).toBe('B');
    expect(RANK_3).toBe('C');
    expect(RANK_4).toBe('D');
    expect(RANK_5).toBe('E');
  });

  it('Ranks object maps rank_N keys to letter grades', () => {
    expect(Ranks).toEqual({
      rank_1: 'A',
      rank_2: 'B',
      rank_3: 'C',
      rank_4: 'D',
      rank_5: 'E',
    });
  });

  it('rank colors are defined as hex values in green-to-red gradient', () => {
    expect(RANK_1_COLOR).toBe('#528f5c');
    expect(RANK_2_COLOR).toBe('#67b373');
    expect(RANK_3_COLOR).toBe('#f9b53f');
    expect(RANK_4_COLOR).toBe('#d4604f');
    expect(RANK_5_COLOR).toBe('#a94c3f');
  });

  it('NO_RATE_COLOR is defined as grey', () => {
    expect(NO_RATE_COLOR).toBe('#d9dde2');
  });
});
