import React from 'react';
import {render} from '@testing-library/react';
import PaginationBar from '../../../Resources/public/js/datagrid/PaginationBar';

const handle = (over = {}) => ({label: 1, title: 'No. 1', ...over});

test('renders one anchor per handle with the base classes and href', () => {
  const {container} = render(
    <PaginationBar handles={[handle(), handle({label: 2, title: 'No. 2'})]} disabled={false} />
  );

  const links = container.querySelectorAll('a.AknActionButton.AknGridToolbar-actionButton');
  expect(links).toHaveLength(2);
  links.forEach(a => expect(a.getAttribute('href')).toBe('#'));
});

test('applies the handle className (active highlight)', () => {
  const {container} = render(
    <PaginationBar handles={[handle({className: 'active AknActionButton--highlight'})]} disabled={false} />
  );

  const a = container.querySelector('a')!;
  expect(a.classList.contains('active')).toBe(true);
  expect(a.classList.contains('AknActionButton--highlight')).toBe(true);
});

test('applies disabled to every handle when disabled is true, absent when false', () => {
  const on = render(<PaginationBar handles={[handle(), handle()]} disabled={true} />);
  on.container.querySelectorAll('a').forEach(a => expect(a.classList.contains('disabled')).toBe(true));

  const off = render(<PaginationBar handles={[handle()]} disabled={false} />);
  expect(off.container.querySelector('a')!.classList.contains('disabled')).toBe(false);
});

test('sets title from the handle, omits the attribute when absent', () => {
  const {container} = render(
    <PaginationBar handles={[handle({title: 'No. 7'}), handle({title: undefined})]} disabled={false} />
  );

  const [withTitle, without] = Array.from(container.querySelectorAll('a'));
  expect(withTitle.getAttribute('title')).toBe('No. 7');
  expect(without.getAttribute('title')).toBeNull();
});

test('applies wrapClass to the inner span, leaves it empty when absent', () => {
  const {container} = render(
    <PaginationBar handles={[handle({wrapClass: 'icon-chevron-left'}), handle()]} disabled={false} />
  );

  const [withWrap, without] = Array.from(container.querySelectorAll('a span'));
  expect(withWrap.className).toBe('icon-chevron-left');
  expect(without.className).toBe('');
});

test('renders the label text including the gap string', () => {
  const {container} = render(
    <PaginationBar
      handles={[
        handle({label: 3, title: 'No. 3'}),
        handle({label: '…', title: '…', className: 'AknActionButton--unclickable'}),
      ]}
      disabled={false}
    />
  );

  expect(container.textContent).toContain('3');
  expect(container.textContent).toContain('…');
});

test('renders nothing for an empty handle list', () => {
  const {container} = render(<PaginationBar handles={[]} disabled={false} />);
  expect(container.querySelectorAll('a')).toHaveLength(0);
});
