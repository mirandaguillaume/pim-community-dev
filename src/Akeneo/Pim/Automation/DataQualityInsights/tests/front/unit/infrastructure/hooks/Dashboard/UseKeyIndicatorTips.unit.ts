import {renderHook} from '@testing-library/react';
import {useGetKeyIndicatorTips} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/Dashboard/UseKeyIndicatorTips';
import {useKeyIndicatorsContext} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';

jest.mock('@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext');

const mockedUseKeyIndicatorsContext = useKeyIndicatorsContext as jest.Mock;

describe('useGetKeyIndicatorTips', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns empty object when tips context has no entry for the given key indicator', () => {
    mockedUseKeyIndicatorsContext.mockReturnValue({tips: {}});
    const {result} = renderHook(() => useGetKeyIndicatorTips('unknown_indicator'));
    expect(result.current).toEqual({});
  });

  it('returns the tips for a known key indicator', () => {
    const tips = {
      completeness: {good: 'Products with all required attributes filled', to_improve: 'Fill in missing attributes'},
    };
    mockedUseKeyIndicatorsContext.mockReturnValue({tips});

    const {result} = renderHook(() => useGetKeyIndicatorTips('completeness'));
    expect(result.current).toEqual(tips.completeness);
  });

  it('returns empty object for a key indicator not present in tips even when other tips exist', () => {
    const tips = {completeness: {good: 'some tip'}};
    mockedUseKeyIndicatorsContext.mockReturnValue({tips});

    const {result} = renderHook(() => useGetKeyIndicatorTips('spelling'));
    expect(result.current).toEqual({});
  });
});
