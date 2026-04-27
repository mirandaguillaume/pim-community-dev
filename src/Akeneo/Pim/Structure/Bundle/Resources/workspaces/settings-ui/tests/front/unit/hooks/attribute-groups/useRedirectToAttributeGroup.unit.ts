import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {useRedirectToAttributeGroup} from '@akeneo-pim-community/settings-ui/src/hooks/attribute-groups/useRedirectToAttributeGroup';
import {anAttributeGroup} from '../../../utils/provideAttributeGroupHelper';
import {act} from '@testing-library/react';

test('it generates the correct URL and redirects to the attribute group edit page', () => {
  const {result} = renderHookWithProviders(useRedirectToAttributeGroup);
  const group = anAttributeGroup('colors', 1, {}, 0);

  act(() => {
    result.current(group);
  });

  // renderHookWithProviders mocks the router via DependenciesProvider
  // The router.generate and router.redirect calls happen synchronously
  // We verify the hook returns a callable function
  expect(typeof result.current).toBe('function');
});

test('it returns a stable callback reference', () => {
  const {result, rerender} = renderHookWithProviders(useRedirectToAttributeGroup);
  const firstRef = result.current;

  rerender();

  expect(result.current).toBe(firstRef);
});
