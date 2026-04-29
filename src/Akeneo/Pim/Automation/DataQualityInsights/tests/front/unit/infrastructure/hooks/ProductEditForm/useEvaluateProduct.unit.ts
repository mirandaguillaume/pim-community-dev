import {renderHook} from '@testing-library/react';
import {useEvaluateProduct} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/ProductEditForm/useEvaluateProduct';

const mockGenerate = jest.fn((route: string, params: any) => `/api/${route}/${params.productId}`);

jest.mock('@akeneo-pim-community/shared', () => ({
  useRouter: () => ({generate: mockGenerate}),
}));

describe('useEvaluateProduct', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns a callback function that posts to the product evaluate route', () => {
    const product = {meta: {id: 'abc-123', model_type: 'product'}, family: 'cameras'};
    const {result} = renderHook(() => useEvaluateProduct(product as any));
    expect(result.current).toBeInstanceOf(Function);
    expect(mockGenerate).toHaveBeenCalledWith('akeneo_data_quality_insights_evaluate_product', {productId: 'abc-123'});
  });

  it('generates the product model evaluate route when model_type is product_model', () => {
    const productModel = {meta: {id: 42, model_type: 'product_model'}, family: 'cameras'};
    renderHook(() => useEvaluateProduct(productModel as any));
    expect(mockGenerate).toHaveBeenCalledWith('akeneo_data_quality_insights_evaluate_product_model', {productId: '42'});
  });

  it('throws when product id is null', () => {
    const product = {meta: {id: null, model_type: 'product'}, family: null};
    expect(() => renderHook(() => useEvaluateProduct(product as any))).toThrow(
      'Product uuid or product model id is not defined'
    );
  });

  it('throws when model_type is neither product nor product_model', () => {
    const product = {meta: {id: 1, model_type: 'unknown_type'}, family: null};
    expect(() => renderHook(() => useEvaluateProduct(product as any))).toThrow('Invalid product type');
  });
});
