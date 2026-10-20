import {render, screen} from '@testing-library/react';
import App from './App';

test('renders the components the built library exports', () => {
  render(<App />);

  expect(screen.getByText('Success')).toBeInTheDocument();
});

test('applies the styled-components wrapper the build produces', () => {
  render(<App />);

  // Asserting on the generated class PREFIX, never its hash. The snapshot this
  // replaced pinned `class="sc-jSFkmK jjMYxm"`, so it failed on any style edit
  // or styled-components bump — reasons that say nothing about whether the
  // build is usable, which is the only thing this project exists to check.
  expect(screen.getByText('Success').className).toMatch(/\bsc-/);
});

test('passes props through to the rendered icon', () => {
  const {container} = render(<App />);

  const icon = container.querySelector('svg');
  expect(icon).not.toBeNull();
  expect(icon).toHaveAttribute('width', '24');
  expect(icon).toHaveAttribute('height', '24');
});
