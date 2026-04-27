import {renderHook} from '@testing-library/react';
import useProduct from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/ProductEditForm/useProduct';
import {useSelector} from 'react-redux';

jest.mock('react-redux', () => ({useSelector: jest.fn()}));

const mockedUseSelector = useSelector as jest.Mock;

describe('useProduct', () => {
  it('returns the product from the redux state', () => {
    const product = {meta: {id: 'abc-123', model_type: 'product'}, family: 'cameras', updated: false};
    mockedUseSelector.mockImplementation((selector: any) => selector({product}));

    const {result} = renderHook(() => useProduct());
    expect(result.current).toEqual(product);
  });

  it('returns a product model when meta.model_type is product_model', () => {
    const productModel = {meta: {id: 42, model_type: 'product_model'}, family: 'cameras', updated: false};
    mockedUseSelector.mockImplementation((selector: any) => selector({product: productModel}));

    const {result} = renderHook(() => useProduct());
    expect(result.current).toEqual(productModel);
  });
});
