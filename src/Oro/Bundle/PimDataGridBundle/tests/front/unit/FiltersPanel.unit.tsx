import React from 'react';
import {render} from '@testing-library/react';
import FiltersPanel from '../../../Resources/public/js/datafilter/FiltersPanel';

describe('FiltersPanel', () => {
  test('appends each filterEl to container on mount', () => {
    const container = document.createElement('div');
    const el1 = document.createElement('div');
    const el2 = document.createElement('div');
    el1.setAttribute('data-name', 'filter-sku');
    el2.setAttribute('data-name', 'filter-family');

    render(<FiltersPanel container={container} filterEls={[el1, el2]} onMounted={jest.fn()} />);

    expect(container.children).toHaveLength(2);
    expect(container.querySelector('[data-name="filter-sku"]')).toBe(el1);
    expect(container.querySelector('[data-name="filter-family"]')).toBe(el2);
  });

  test('calls onMounted after all elements are appended', () => {
    const container = document.createElement('div');
    const el = document.createElement('div');
    let countAtCallTime = 0;
    const onMounted = jest.fn(() => {
      countAtCallTime = container.children.length;
    });

    render(<FiltersPanel container={container} filterEls={[el]} onMounted={onMounted} />);

    expect(onMounted).toHaveBeenCalledTimes(1);
    expect(countAtCallTime).toBe(1);
  });

  test('calls onMounted even when filterEls is empty', () => {
    const container = document.createElement('div');
    const onMounted = jest.fn();

    render(<FiltersPanel container={container} filterEls={[]} onMounted={onMounted} />);

    expect(container.children).toHaveLength(0);
    expect(onMounted).toHaveBeenCalledTimes(1);
  });

  test('renders null — no DOM output from React itself', () => {
    const container = document.createElement('div');
    const {container: wrapper} = render(<FiltersPanel container={container} filterEls={[]} onMounted={jest.fn()} />);

    expect(wrapper).toBeEmptyDOMElement();
  });

  test('re-runs effect when filterEls prop changes', () => {
    const container = document.createElement('div');
    const el1 = document.createElement('div');
    const el2 = document.createElement('div');
    const onMounted = jest.fn();

    const {rerender} = render(<FiltersPanel container={container} filterEls={[el1]} onMounted={onMounted} />);
    expect(container.children).toHaveLength(1);
    expect(onMounted).toHaveBeenCalledTimes(1);

    rerender(<FiltersPanel container={container} filterEls={[el1, el2]} onMounted={onMounted} />);
    expect(container.children).toHaveLength(2);
    expect(onMounted).toHaveBeenCalledTimes(2);
  });
});
