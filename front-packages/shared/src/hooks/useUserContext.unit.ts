import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useUserContext} from './useUserContext';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useUserContext())).toThrow(
    '[DependenciesContext]: UserContext has not been properly initiated'
  );
});

test('it returns the UserContext', () => {
  const {result} = renderHookWithProviders(() => useUserContext());

  expect(result.current).not.toBeNull();
});
