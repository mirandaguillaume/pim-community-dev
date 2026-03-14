import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useSecurity} from './useSecurity';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useSecurity())).toThrow('[DependenciesContext]: Security has not been properly initiated');
});

test('it returns the Security', () => {
  const {result} = renderHookWithProviders(() => useSecurity());

  expect(result.current).not.toBeNull();
});
