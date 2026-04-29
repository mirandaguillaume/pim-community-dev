import {renderHook} from '@testing-library/react';
import useCatalogContext from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/ProductEditForm/useCatalogContext';
import {useSelector} from 'react-redux';

jest.mock('react-redux', () => ({useSelector: jest.fn()}));

const mockedUseSelector = useSelector as jest.Mock;

describe('useCatalogContext', () => {
  it('extracts locale and channel from the redux catalog context state', () => {
    mockedUseSelector.mockImplementation((selector: any) =>
      selector({catalogContext: {locale: 'en_US', channel: 'ecommerce'}})
    );

    const {result} = renderHook(() => useCatalogContext());
    expect(result.current).toEqual({locale: 'en_US', channel: 'ecommerce'});
  });

  it('returns updated values when state changes', () => {
    mockedUseSelector.mockImplementation((selector: any) =>
      selector({catalogContext: {locale: 'fr_FR', channel: 'mobile'}})
    );

    const {result} = renderHook(() => useCatalogContext());
    expect(result.current).toEqual({locale: 'fr_FR', channel: 'mobile'});
  });
});
