import {getQualityBadgeLevel} from 'akeneopimstructure/js/attribute-option/helper/getQualityBadgeLevel';

describe('getQualityBadgeLevel', () => {
  it("returns 'primary' for 'good'", () => {
    expect(getQualityBadgeLevel('good')).toBe('primary');
  });

  it("returns 'danger' for 'to_improve'", () => {
    expect(getQualityBadgeLevel('to_improve')).toBe('danger');
  });

  it("returns 'warning' for 'in_progress'", () => {
    expect(getQualityBadgeLevel('in_progress')).toBe('warning');
  });

  it("returns 'tertiary' for any unknown label", () => {
    expect(getQualityBadgeLevel('unknown')).toBe('tertiary');
    expect(getQualityBadgeLevel('')).toBe('tertiary');
  });
});
