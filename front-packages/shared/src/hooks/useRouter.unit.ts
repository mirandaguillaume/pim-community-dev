import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useRouter} from './useRouter';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useRouter())).toThrow('[DependenciesContext]: Router has not been properly initiated');
});

test('it returns the Router', () => {
  const {result} = renderHookWithProviders(() => useRouter());

  expect(result.current).not.toBeNull();
});
