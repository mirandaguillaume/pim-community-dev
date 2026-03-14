import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useSystemConfiguration} from './useSystemConfiguration';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useSystemConfiguration())).toThrow(
    '[DependenciesContext]: SystemConfiguration has not been properly initiated'
  );
});

test('it returns the SystemConfiguration', () => {
  const {result} = renderHookWithProviders(() => useSystemConfiguration());

  expect(result.current).not.toBeNull();
});
