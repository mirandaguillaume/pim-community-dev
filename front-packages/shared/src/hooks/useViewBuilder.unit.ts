import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useViewBuilder} from './useViewBuilder';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useViewBuilder())).toThrow('[DependenciesContext]: ViewBuilder has not been properly initiated');
});

test('it returns the ViewBuilder', () => {
  const {result} = renderHookWithProviders(() => useViewBuilder());

  expect(result.current).not.toBeNull();
});
