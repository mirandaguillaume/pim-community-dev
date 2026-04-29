import {renderHook, waitFor} from '@testing-library/react';
import useProductEvaluation from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/ProductEditForm/useProductEvaluation';
import {useSelector, useDispatch} from 'react-redux';

jest.mock('react-redux', () => ({
  useSelector: jest.fn(),
  useDispatch: jest.fn(),
}));

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/reducer', () => ({
  getProductEvaluationAction: jest.fn((productId: any) => ({type: 'GET_PRODUCT_EVALUATION', productId})),
}));

const mockedUseSelector = useSelector as jest.Mock;
const mockedUseDispatch = useDispatch as jest.Mock;

describe('useProductEvaluation', () => {
  const mockDispatch = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
    mockedUseDispatch.mockReturnValue(mockDispatch);
  });

  it('returns evaluation, productId and productUpdated from state', () => {
    const evaluation = {completeness: {rate: 75}};
    const state = {
      product: {meta: {id: 'abc-123'}, updated: false},
      productEvaluation: {'abc-123': evaluation},
    };
    mockedUseSelector.mockImplementation((selector: any) => selector(state));

    const {result} = renderHook(() => useProductEvaluation());
    expect(result.current.productId).toBe('abc-123');
    expect(result.current.evaluation).toEqual(evaluation);
    expect(result.current.productUpdated).toBe(false);
  });

  it('dispatches getProductEvaluationAction when evaluation is undefined', async () => {
    const state = {
      product: {meta: {id: 'abc-123'}, updated: false},
      productEvaluation: {},
    };
    mockedUseSelector.mockImplementation((selector: any) => selector(state));

    renderHook(() => useProductEvaluation());

    await waitFor(() =>
      expect(mockDispatch).toHaveBeenCalledWith(
        expect.objectContaining({type: 'GET_PRODUCT_EVALUATION', productId: 'abc-123'})
      )
    );
  });

  it('does not dispatch when productId is null', () => {
    const state = {
      product: {meta: {id: null}, updated: false},
      productEvaluation: {},
    };
    mockedUseSelector.mockImplementation((selector: any) => selector(state));

    renderHook(() => useProductEvaluation());
    expect(mockDispatch).not.toHaveBeenCalled();
  });
});
