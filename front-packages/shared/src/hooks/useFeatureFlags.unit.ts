import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useFeatureFlags} from './useFeatureFlags';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useFeatureFlags())).toThrow('[DependenciesContext]: FeatureFlags has not been properly initiated');
});

test('it returns the FeatureFlags', () => {
  const {result} = renderHookWithProviders(() => useFeatureFlags());

  expect(result.current).not.toBeNull();
});
