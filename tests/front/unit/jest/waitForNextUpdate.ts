import {act} from '@testing-library/react';

/**
 * Compatibility helper replacing @testing-library/react-hooks' waitForNextUpdate.
 * Flushes microtasks (Promise resolutions) and React state updates.
 */
export async function waitForNextUpdate() {
  await act(async () => {
    await new Promise(resolve => setTimeout(resolve, 0));
  });
}
