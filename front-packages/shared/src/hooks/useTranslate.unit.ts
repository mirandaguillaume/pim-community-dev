import {renderHook} from '@testing-library/react';
import {renderHookWithProviders} from '../tests/utils';
import {useTranslate} from './useTranslate';

test('it throws when the provider is not found', () => {
  expect(() => renderHook(() => useTranslate())).toThrow('[DependenciesContext]: Translate has not been properly initiated');
});

test('it returns the Translate', () => {
  const {result} = renderHookWithProviders(() => useTranslate());

  expect(result.current).not.toBeNull();
});
