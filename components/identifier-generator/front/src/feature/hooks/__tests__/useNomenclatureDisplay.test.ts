import {renderHook} from '@testing-library/react';
import {useNomenclatureDisplay} from '../useNomenclatureDisplay';
import {Nomenclature, Operator} from '../../models';

const baseNomenclature: Nomenclature = {
  propertyCode: 'family',
  operator: Operator.EQUALS,
  generate_if_empty: false,
  value: 3,
  values: {},
};

describe('useNomenclatureDisplay - getPlaceholder', () => {
  it('should return first N chars of code when generate_if_empty is true', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, generate_if_empty: true, value: 3};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.getPlaceholder('ABCDEF')).toBe('ABC');
  });

  it('should return empty string when generate_if_empty is false', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, generate_if_empty: false};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.getPlaceholder('ABCDEF')).toBe('');
  });

  it('should return empty string when nomenclature is undefined', () => {
    const {result} = renderHook(() => useNomenclatureDisplay(undefined));
    expect(result.current.getPlaceholder('ABCDEF')).toBe('');
  });
});

describe('useNomenclatureDisplay - isValid', () => {
  it('should return true when nomenclature is undefined', () => {
    const {result} = renderHook(() => useNomenclatureDisplay(undefined));
    expect(result.current.isValid('ABC')).toBe(true);
  });

  it('should return true when value is null', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, value: null};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('ABC')).toBe(true);
  });

  it('should return true when operator is null', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, operator: null};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('ABC')).toBe(true);
  });

  it('should return true when generate_if_empty is true and value is empty string', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, generate_if_empty: true, value: 3};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('')).toBe(true);
  });

  it('should return true for EQUALS operator when length matches value', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, operator: Operator.EQUALS, value: 3};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('ABC')).toBe(true);
  });

  it('should return false for EQUALS operator when length does not match value', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, operator: Operator.EQUALS, value: 3};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('AB')).toBe(false);
  });

  it('should return true for LOWER_OR_EQUAL_THAN operator when length is less than or equal to value', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, operator: Operator.LOWER_OR_EQUAL_THAN, value: 5};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('ABC')).toBe(true);
  });

  it('should return true for LOWER_OR_EQUAL_THAN operator when length equals value', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, operator: Operator.LOWER_OR_EQUAL_THAN, value: 3};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('ABC')).toBe(true);
  });

  it('should return false for LOWER_OR_EQUAL_THAN operator when length exceeds value', () => {
    const nomenclature: Nomenclature = {...baseNomenclature, operator: Operator.LOWER_OR_EQUAL_THAN, value: 3};
    const {result} = renderHook(() => useNomenclatureDisplay(nomenclature));
    expect(result.current.isValid('ABCD')).toBe(false);
  });
});
