import {getUnitLabel} from './unit';

describe('getUnitLabel', () => {
  it('returns the label when the locale exists', () => {
    const unit = {
      code: 'METER',
      labels: {
        en_US: 'Meters',
        fr_FR: 'Mètres',
      },
      symbol: 'm',
      convert_from_standard: [{operator: 'mul', value: '1'}],
    };

    expect(getUnitLabel(unit, 'en_US')).toBe('Meters');
    expect(getUnitLabel(unit, 'fr_FR')).toBe('Mètres');
  });

  it('falls back to the code in brackets when the locale is missing', () => {
    const unit = {
      code: 'METER',
      labels: {
        en_US: 'Meters',
      },
      symbol: 'm',
      convert_from_standard: [{operator: 'mul', value: '1'}],
    };

    expect(getUnitLabel(unit, 'de_DE')).toBe('[METER]');
  });
});
