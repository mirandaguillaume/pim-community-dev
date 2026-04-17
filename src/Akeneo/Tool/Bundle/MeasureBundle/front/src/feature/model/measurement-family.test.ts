import {
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
  getStandardUnit,
  setMeasurementFamilyLabel,
  setUnitLabel,
  setUnitOperations,
  setUnitSymbol,
  sortMeasurementFamily,
  filterOnLabelOrCode,
  getUnitIndex,
  getUnit,
  removeUnit,
  addUnit,
  MeasurementFamily,
} from './measurement-family';

const measurementFamily: MeasurementFamily = {
  code: 'AREA',
  labels: {
    en_US: 'Area',
  },
  standard_unit_code: 'SQUARE_METER',
  units: [
    {
      code: 'SQUARE_METER',
      labels: {
        en_US: 'Square Meter',
      },
      symbol: '',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    },
    {
      code: 'SQUARE_KILOMETER',
      labels: {
        en_US: 'Square Kilometer',
      },
      symbol: '',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1000',
        },
      ],
    },
  ],
  is_locked: false,
};

describe('measurement family', () => {
  it('should provide a label', () => {
    const label = getMeasurementFamilyLabel(measurementFamily, 'en_US');

    expect(label).toEqual('Area');
  });

  it('should provide a fallback label', () => {
    const label = getMeasurementFamilyLabel(measurementFamily, 'fr_FR');

    expect(label).toEqual('[AREA]');
  });

  it('should provide a unit label', () => {
    const label = getStandardUnitLabel(measurementFamily, 'en_US');

    expect(label).toEqual('Square Meter');
  });

  it('should provide a unit fallback label', () => {
    const label = getStandardUnitLabel(measurementFamily, 'fr_FR');

    expect(label).toEqual('[SQUARE_METER]');
  });

  it('should provide a unit fallback label if label does not exist', () => {
    const label = getStandardUnitLabel(measurementFamily, 'fr_FR');

    expect(label).toEqual('[SQUARE_METER]');
  });

  it('should set the provided label on the measurement family', () => {
    const newMeasurementFamily = setMeasurementFamilyLabel(measurementFamily, 'fr_FR', 'Aire');

    expect(newMeasurementFamily.labels.fr_FR).toEqual('Aire');
  });

  it('should set the provided label on the unit in the measurement family', () => {
    const newMeasurementFamily = setUnitLabel(measurementFamily, 'SQUARE_METER', 'fr_FR', 'Mètre carré');

    expect(newMeasurementFamily.units[0].labels.fr_FR).toEqual('Mètre carré');
  });

  it('should set the provided operations on the unit in the measurement family', () => {
    const newMeasurementFamily = setUnitOperations(measurementFamily, 'SQUARE_METER', [{operator: 'div', value: '3'}]);

    expect(newMeasurementFamily.units[0].convert_from_standard).toEqual([{operator: 'div', value: '3'}]);
  });

  // Kills: ConditionalExpression line 47 (if (false)) and BlockStatement line 47 (emptied block)
  it('should preserve non-matching units when setting operations on a specific unit', () => {
    const newMeasurementFamily = setUnitOperations(measurementFamily, 'SQUARE_METER', [{operator: 'div', value: '3'}]);

    // The second unit must remain unchanged
    expect(newMeasurementFamily.units[1]).toEqual(measurementFamily.units[1]);
    expect(newMeasurementFamily.units[1].code).toEqual('SQUARE_KILOMETER');
    expect(newMeasurementFamily.units[1].convert_from_standard).toEqual([{operator: 'mul', value: '1000'}]);
    // The array length must be preserved
    expect(newMeasurementFamily.units).toHaveLength(2);
  });

  it('should remove the provided unit (using the unit code) from the measurement family', () => {
    expect(measurementFamily.units.length).toEqual(2);

    const newMeasurementFamily = removeUnit(measurementFamily, 'SQUARE_KILOMETER');

    expect(newMeasurementFamily.units.length).toEqual(1);
  });

  it('should add the provided unit in the measurement family', () => {
    expect(measurementFamily.units.length).toEqual(2);

    const newMeasurementFamily = addUnit(measurementFamily, {
      code: 'CUSTOM',
      labels: {
        en_US: 'Custom',
      },
      symbol: 'c',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    });

    expect(newMeasurementFamily.units.length).toEqual(3);
  });

  it('should set the provided symbol on the unit in the measurement family', () => {
    const newMeasurementFamily = setUnitSymbol(measurementFamily, 'SQUARE_METER', 'new symbol');

    expect(newMeasurementFamily.units[0].symbol).toEqual('new symbol');
  });

  it('should return the unit index in the measurement family unit list', () => {
    expect(getUnitIndex(measurementFamily, 'SQUARE_METER')).toEqual(0);
    expect(getUnitIndex(measurementFamily, 'UNKNOWN')).toEqual(-1);
  });

  // Kills: ConditionalExpression line 91 (if (false) return -1)
  it('should return -1 from getUnitIndex when the unit does not exist', () => {
    const result = getUnitIndex(measurementFamily, 'DOES_NOT_EXIST');
    expect(result).toBe(-1);
  });

  it('should return the correct unit from getUnit', () => {
    const unit = getUnit(measurementFamily, 'SQUARE_METER');
    expect(unit).toBeDefined();
    expect(unit!.code).toBe('SQUARE_METER');
  });

  it('should return undefined from getUnit when the unit does not exist', () => {
    const unit = getUnit(measurementFamily, 'DOES_NOT_EXIST');
    expect(unit).toBeUndefined();
  });

  it('should return the standard unit from the measurement family', () => {
    expect(getStandardUnit(measurementFamily)).toEqual({
      code: 'SQUARE_METER',
      labels: {
        en_US: 'Square Meter',
      },
      symbol: '',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    });
  });

  // Kills: StringLiteral line 99 (error message emptied to "")
  it('should throw with a specific error message if the standard unit is not found', () => {
    expect(() => getStandardUnit({...measurementFamily, standard_unit_code: 'UNKNOWN'})).toThrowError(
      'Measurement family should always have a standard unit'
    );
  });

  it('should filter a measurement family on label and code', () => {
    expect(
      filterOnLabelOrCode(
        're',
        'en_US'
      )({
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
      })
    ).toEqual(true);

    expect(
      filterOnLabelOrCode(
        're',
        'fr_FR'
      )({
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
      })
    ).toEqual(true);

    expect(
      filterOnLabelOrCode(
        'nice',
        'fr_FR'
      )({
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
      })
    ).toEqual(false);

    expect(
      filterOnLabelOrCode(
        'aire',
        'fr_FR'
      )({
        code: 'AREA',
        labels: {
          fr_FR: 'Aire',
        },
      })
    ).toEqual(true);
  });

  it('should sort two measurement families', () => {
    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'label'
      )(
        {
          code: 'AREA',
          labels: {
            fr_FR: 'Aire',
          },
        } as MeasurementFamily,
        {
          code: 'AREB',
          labels: {
            fr_FR: 'Aire',
          },
        } as MeasurementFamily
      )
    ).toEqual(-1);

    expect(
      sortMeasurementFamily(
        'descending',
        'en_US',
        'label'
      )(
        {
          code: 'ARE',
          labels: {
            en_US: 'Aireb',
          },
        } as MeasurementFamily,
        {
          code: 'AREB',
          labels: {
            en_US: 'Airea',
          },
        } as MeasurementFamily
      )
    ).toEqual(-1);

    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'code'
      )(
        {
          code: 'AREA',
          labels: {
            fr_FR: 'Aire',
          },
        } as MeasurementFamily,
        {
          code: 'AREB',
          labels: {
            fr_FR: 'Aire',
          },
        } as MeasurementFamily
      )
    ).toEqual(-1);

    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'standard_unit'
      )(
        {
          code: 'AREA',
          standard_unit_code: 'SQUARE_METER',
          labels: {
            fr_FR: 'Aire',
          },
          units: [
            {
              code: 'SQUARE_METER',
              labels: {
                en_US: 'Square Meter',
              },
            },
          ],
          is_locked: false,
        } as MeasurementFamily,
        {
          code: 'AREB',
          standard_unit_code: 'SQUARE_METEB',
          labels: {
            fr_FR: 'Aire',
          },
          units: [
            {
              code: 'SQUARE_METEB',
              labels: {
                en_US: 'Square Meteb',
              },
            },
          ],
          is_locked: false,
        } as MeasurementFamily
      )
    ).toEqual(1);

    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'unit_count'
      )(
        {
          code: 'AREA',
          standard_unit_code: 'SQUARE_METER',
          labels: {
            fr_FR: 'Aire',
          },
          units: [
            {
              code: 'SQUARE_METER',
              labels: {
                en_US: 'Square Meter',
              },
            },
          ],
          is_locked: false,
        } as MeasurementFamily,
        {
          code: 'AREB',
          standard_unit_code: 'SQUARE_METEB',
          labels: {
            fr_FR: 'Aire',
          },
          units: [],
          is_locked: false,
        } as MeasurementFamily
      )
    ).toEqual(1);

    expect(
      sortMeasurementFamily('ascending', 'en_US', 'yolo')({} as MeasurementFamily, {} as MeasurementFamily)
    ).toEqual(0);
  });

  // Kills: ArithmeticOperator line 122 (* -> / for label sort)
  // When two families have the same label (localeCompare returns 0),
  // directionInverter * 0 = 0, but directionInverter / 0 = Infinity
  it('should return 0 when sorting by label and both labels are equal', () => {
    const result = sortMeasurementFamily(
      'ascending',
      'en_US',
      'label'
    )(
      {
        code: 'AREA',
        labels: {en_US: 'Same'},
      } as MeasurementFamily,
      {
        code: 'LENGTH',
        labels: {en_US: 'Same'},
      } as MeasurementFamily
    );

    expect(result).toBe(0);
  });

  // Kills: ArithmeticOperator line 126 (* -> / for code sort)
  it('should return 0 when sorting by code and both codes are equal', () => {
    const result = sortMeasurementFamily(
      'ascending',
      'en_US',
      'code'
    )(
      {
        code: 'SAME_CODE',
        labels: {en_US: 'First'},
      } as MeasurementFamily,
      {
        code: 'SAME_CODE',
        labels: {en_US: 'Second'},
      } as MeasurementFamily
    );

    expect(result).toBe(0);
  });

  // Kills: ArithmeticOperator line 129 (* -> / for standard_unit sort)
  it('should return 0 when sorting by standard_unit and both standard unit labels are equal', () => {
    const result = sortMeasurementFamily(
      'ascending',
      'en_US',
      'standard_unit'
    )(
      {
        code: 'AREA',
        standard_unit_code: 'UNIT_A',
        labels: {},
        units: [{code: 'UNIT_A', labels: {en_US: 'Same Unit'}}],
        is_locked: false,
      } as MeasurementFamily,
      {
        code: 'LENGTH',
        standard_unit_code: 'UNIT_B',
        labels: {},
        units: [{code: 'UNIT_B', labels: {en_US: 'Same Unit'}}],
        is_locked: false,
      } as MeasurementFamily
    );

    expect(result).toBe(0);
  });

  // Kills: ArithmeticOperator line 132 (* -> / for unit_count)
  it('should return 0 when sorting by unit_count and both families have the same number of units', () => {
    const result = sortMeasurementFamily(
      'ascending',
      'en_US',
      'unit_count'
    )(
      {
        code: 'AREA',
        standard_unit_code: 'UNIT_A',
        labels: {},
        units: [{code: 'UNIT_A', labels: {en_US: 'A'}}],
        is_locked: false,
      } as MeasurementFamily,
      {
        code: 'LENGTH',
        standard_unit_code: 'UNIT_B',
        labels: {},
        units: [{code: 'UNIT_B', labels: {en_US: 'B'}}],
        is_locked: false,
      } as MeasurementFamily
    );

    expect(result).toBe(0);
  });

  // Kills: ArithmeticOperator line 132 (- -> + for unit_count subtraction)
  // first has fewer units than second: first.units.length - second.units.length < 0
  // but with +: first.units.length + second.units.length > 0
  it('should sort by unit_count ascending: fewer units first', () => {
    const result = sortMeasurementFamily(
      'ascending',
      'en_US',
      'unit_count'
    )(
      {
        code: 'FEW',
        standard_unit_code: 'A',
        labels: {},
        units: [{code: 'A', labels: {}}],
        is_locked: false,
      } as MeasurementFamily,
      {
        code: 'MANY',
        standard_unit_code: 'B',
        labels: {},
        units: [
          {code: 'B', labels: {}},
          {code: 'C', labels: {}},
          {code: 'D', labels: {}},
        ],
        is_locked: false,
      } as MeasurementFamily
    );

    expect(result).toBeLessThan(0);
  });
});
