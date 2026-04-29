import {renderHook} from '@testing-library/react';
import useProductFamily from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/ProductEditForm/useProductFamily';
import {useSelector} from 'react-redux';

jest.mock('react-redux', () => ({useSelector: jest.fn()}));

const mockedUseSelector = useSelector as jest.Mock;

describe('useProductFamily', () => {
  it('returns the family matching the product family code', () => {
    const state = {
      product: {family: 'cameras', meta: {id: 1}},
      families: {cameras: {code: 'cameras', labels: {en_US: 'Cameras'}, attributes: []}},
    };
    mockedUseSelector.mockImplementation((selector: any) => selector(state));

    const {result} = renderHook(() => useProductFamily());
    expect(result.current).toEqual(state.families.cameras);
  });

  it('returns null when the product has no family (family is null)', () => {
    const state = {
      product: {family: null, meta: {id: 1}},
      families: {cameras: {code: 'cameras', labels: {}}},
    };
    mockedUseSelector.mockImplementation((selector: any) => selector(state));

    const {result} = renderHook(() => useProductFamily());
    expect(result.current).toBeNull();
  });

  it('returns undefined when family code exists but is not found in families map', () => {
    const state = {
      product: {family: 'unknown_family', meta: {id: 1}},
      families: {},
    };
    mockedUseSelector.mockImplementation((selector: any) => selector(state));

    const {result} = renderHook(() => useProductFamily());
    expect(result.current).toBeUndefined();
  });
});
