import {renderHook} from '@testing-library/react';
import usePageContext from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/ProductEditForm/usePageContext';
import {useSelector} from 'react-redux';

jest.mock('react-redux', () => ({useSelector: jest.fn()}));

const mockedUseSelector = useSelector as jest.Mock;

describe('usePageContext', () => {
  it('returns the page context from the redux state', () => {
    const pageContext = {productType: 'product', comparisonEnabled: false, scopeCode: 'ecommerce', localeCode: 'en_US'};
    mockedUseSelector.mockImplementation((selector: any) => selector({pageContext}));

    const {result} = renderHook(() => usePageContext());
    expect(result.current).toEqual(pageContext);
  });

  it('returns page context for a product model', () => {
    const pageContext = {
      productType: 'product_model',
      comparisonEnabled: true,
      scopeCode: 'mobile',
      localeCode: 'fr_FR',
    };
    mockedUseSelector.mockImplementation((selector: any) => selector({pageContext}));

    const {result} = renderHook(() => usePageContext());
    expect(result.current).toEqual(pageContext);
  });
});
