import {renderHook, waitFor, act} from '@testing-library/react';
import {useDispatch} from 'react-redux';
import useFetchProductDataQualityEvaluation from '../../../../../../front/src/infrastructure/hooks/ProductEditForm/useFetchProductDataQualityEvaluation';
import {useCatalogContext, useProductEvaluation} from '../../../../../../front/src/infrastructure/hooks/index';

jest.mock('react-redux', () => ({useDispatch: jest.fn()}));

jest.mock('../../../../../../front/src/infrastructure/hooks/index', () => ({
  useCatalogContext: jest.fn(),
  useProductEvaluation: jest.fn(),
}));

jest.mock('../../../../../../front/src/infrastructure/reducer', () => ({
  getProductEvaluationAction: jest.fn((productId: string, data: any) => ({
    type: 'GET_PRODUCT_EVALUATION',
    productId,
    data,
  })),
}));

const mockedUseDispatch = useDispatch as jest.Mock;
const mockedUseCatalogContext = useCatalogContext as jest.Mock;
const mockedUseProductEvaluation = useProductEvaluation as jest.Mock;

describe('useFetchProductDataQualityEvaluation', () => {
  const mockDispatch = jest.fn();
  const mockFetcher = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
    jest.useFakeTimers();
    mockedUseDispatch.mockReturnValue(mockDispatch);
    mockedUseCatalogContext.mockReturnValue({channel: 'ecommerce', locale: 'en_US'});
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('returns evaluation from state', () => {
    const evaluation = {ecommerce: {en_US: [{status: 'done', criterionCode: 'completeness'}]}};
    mockedUseProductEvaluation.mockReturnValue({productId: 'abc-123', productUpdated: false, evaluation});

    const {result} = renderHook(() => useFetchProductDataQualityEvaluation(mockFetcher));
    expect(result.current).toEqual(evaluation);
  });

  it('returns undefined when evaluation is not yet loaded', () => {
    mockedUseProductEvaluation.mockReturnValue({productId: null, productUpdated: false, evaluation: undefined});

    const {result} = renderHook(() => useFetchProductDataQualityEvaluation(mockFetcher));
    expect(result.current).toBeUndefined();
  });

  it('calls fetcher with productId when product is updated', async () => {
    const evaluation = {ecommerce: {en_US: [{status: 'done', criterionCode: 'completeness'}]}};
    mockFetcher.mockResolvedValue(evaluation);
    mockedUseProductEvaluation.mockReturnValue({productId: 'abc-123', productUpdated: true, evaluation});

    renderHook(() => useFetchProductDataQualityEvaluation(mockFetcher));

    act(() => jest.runAllTimers());
    await waitFor(() => expect(mockFetcher).toHaveBeenCalledWith('abc-123'));
  });

  it('does not call fetcher when productId is null', () => {
    mockedUseProductEvaluation.mockReturnValue({productId: null, productUpdated: true, evaluation: undefined});

    renderHook(() => useFetchProductDataQualityEvaluation(mockFetcher));
    act(() => jest.runAllTimers());
    expect(mockFetcher).not.toHaveBeenCalled();
  });

  it('does not call fetcher when product is not updated', () => {
    const evaluation = {ecommerce: {en_US: [{status: 'done'}]}};
    mockedUseProductEvaluation.mockReturnValue({productId: 'abc-123', productUpdated: false, evaluation});

    renderHook(() => useFetchProductDataQualityEvaluation(mockFetcher));
    act(() => jest.runAllTimers());
    expect(mockFetcher).not.toHaveBeenCalled();
  });

  it('dispatches action after fetching evaluation', async () => {
    const evaluationData = {ecommerce: {en_US: [{status: 'done'}]}};
    mockFetcher.mockResolvedValue(evaluationData);
    mockedUseProductEvaluation.mockReturnValue({
      productId: 'abc-123',
      productUpdated: true,
      evaluation: evaluationData,
    });

    renderHook(() => useFetchProductDataQualityEvaluation(mockFetcher));
    act(() => jest.runAllTimers());

    await waitFor(() =>
      expect(mockDispatch).toHaveBeenCalledWith(
        expect.objectContaining({type: 'GET_PRODUCT_EVALUATION', productId: 'abc-123'})
      )
    );
  });
});
