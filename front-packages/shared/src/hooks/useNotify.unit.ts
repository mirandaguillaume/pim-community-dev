import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useNotify} from './useNotify';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useNotify())).toThrow('[DependenciesContext]: Notify has not been properly initiated');
});

test('it returns the Notify', () => {
  const {result} = renderHookWithProviders(() => useNotify());

  expect(result.current).not.toBeNull();
});
