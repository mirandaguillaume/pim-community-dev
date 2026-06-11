import React from 'react';
import {render, fireEvent} from '@testing-library/react';
import {ProductImage} from '../../../Resources/public/js/datagrid/cell/ProductImage';

test('renders a plain product image without the layer', () => {
  const {container} = render(
    <ProductImage src="/media/a.jpg" fallbackSrc="/fallback.jpg" label="A label" stacked={false} />
  );
  const img = container.querySelector('img')!;

  expect(img.className).toBe('AknGrid-image');
  expect(img.getAttribute('src')).toBe('/media/a.jpg');
  expect(img.getAttribute('title')).toBe('A label');
  expect(container.querySelector('.AknGrid-imageLayer')).toBeNull();
});

test('renders a stacked image with the layer for product models', () => {
  const {container} = render(
    <ProductImage src="/media/a.jpg" fallbackSrc="/fallback.jpg" label="A label" stacked={true} />
  );
  const img = container.querySelector('img')!;

  expect(img.className).toBe('AknGrid-image AknGrid-image--withLayer');
  expect(container.querySelector('.AknGrid-imageLayer')).not.toBeNull();
});

test('swaps to the fallback src once on a load error', () => {
  const {container} = render(<ProductImage src="/broken.jpg" fallbackSrc="/fallback.jpg" label="x" stacked={false} />);
  const img = container.querySelector('img')!;

  fireEvent.error(img);
  expect(img.getAttribute('src')).toBe('/fallback.jpg');

  // A second error must not loop — the one-shot guard keeps the fallback src.
  fireEvent.error(img);
  expect(img.getAttribute('src')).toBe('/fallback.jpg');
});
