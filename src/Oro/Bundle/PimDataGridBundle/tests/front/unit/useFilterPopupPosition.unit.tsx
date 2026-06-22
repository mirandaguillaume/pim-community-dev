import React, {useRef} from 'react';
import {render, fireEvent} from '@testing-library/react';
import {
  computeFilterPopupPosition,
  useFilterPopupPosition,
} from '../../../Resources/public/js/datafilter/filter/useFilterPopupPosition';

describe('computeFilterPopupPosition (legacy flip/clamp math)', () => {
  const viewport = {width: 1000, height: 800};
  const popup = {left: 0, top: 0, width: 200, height: 150};

  test('keeps the popup at the anchor top-left when it fits', () => {
    expect(computeFilterPopupPosition({left: 10, top: 20, width: 100, height: 30}, popup, viewport)).toEqual({
      left: 10,
      top: 20,
    });
  });

  test('flips left when overflowing the right edge with room', () => {
    expect(computeFilterPopupPosition({left: 900, top: 20, width: 100, height: 30}, popup, viewport)).toEqual({
      left: 800,
      top: 20,
    });
  });

  test('stays at the anchor left when overflowing right but with no room to flip', () => {
    expect(
      computeFilterPopupPosition({left: 50, top: 20, width: 30, height: 30}, popup, {width: 100, height: 800})
    ).toEqual({left: 50, top: 20});
  });

  test('flips up when overflowing the bottom with room', () => {
    expect(computeFilterPopupPosition({left: 10, top: 700, width: 100, height: 30}, popup, viewport)).toEqual({
      left: 10,
      top: 580,
    });
  });

  test('clamps to top 0 when overflowing the bottom with no room up', () => {
    expect(
      computeFilterPopupPosition({left: 10, top: 100, width: 100, height: 30}, popup, {width: 1000, height: 200})
    ).toEqual({left: 10, top: 0});
  });
});

const setRect = (el: HTMLElement, rect: {left?: number; top?: number; width?: number; height?: number}): void => {
  el.getBoundingClientRect = () =>
    ({left: 0, top: 0, width: 0, height: 0, right: 0, bottom: 0, x: 0, y: 0, toJSON: () => ({}), ...rect}) as DOMRect;
};

const Harness = ({isOpen}: {isOpen: boolean}) => {
  const ref = useRef<HTMLDivElement>(null);
  useFilterPopupPosition(ref, isOpen);
  return (
    <div className="column-inner">
      <div className="anchor">
        <div ref={ref} className="popup">
          criteria
        </div>
      </div>
    </div>
  );
};

describe('useFilterPopupPosition', () => {
  const setViewport = (width: number, height: number): void => {
    Object.defineProperty(document.body, 'clientWidth', {value: width, configurable: true});
    Object.defineProperty(document.body, 'clientHeight', {value: height, configurable: true});
  };

  test('positions the popup fixed, aligned with its anchor, on resize while open', () => {
    const {container} = render(<Harness isOpen={true} />);
    const anchor = container.querySelector('.anchor') as HTMLElement;
    const popup = container.querySelector('.popup') as HTMLElement;
    setRect(anchor, {left: 900, top: 20, width: 100, height: 30});
    setRect(popup, {width: 200, height: 150});
    setViewport(1000, 800);

    fireEvent.resize(window);

    expect(popup.style.position).toBe('fixed');
    expect(popup.style.left).toBe('800px'); // flipped left
    expect(popup.style.top).toBe('20px');
  });

  test('re-positions on the closest .column-inner scroll', () => {
    const {container} = render(<Harness isOpen={true} />);
    const anchor = container.querySelector('.anchor') as HTMLElement;
    const popup = container.querySelector('.popup') as HTMLElement;
    const columnInner = container.querySelector('.column-inner') as HTMLElement;
    setRect(anchor, {left: 10, top: 700, width: 100, height: 30});
    setRect(popup, {width: 200, height: 150});
    setViewport(1000, 800);

    fireEvent.scroll(columnInner);

    expect(popup.style.top).toBe('580px'); // flipped up
  });

  test('does not touch the popup style when closed', () => {
    const {container} = render(<Harness isOpen={false} />);
    const popup = container.querySelector('.popup') as HTMLElement;

    expect(popup.style.position).toBe('');
  });

  test('removes its scroll/resize listeners when the popup closes', () => {
    const removeSpy = jest.spyOn(window, 'removeEventListener');
    const {rerender} = render(<Harness isOpen={true} />);

    rerender(<Harness isOpen={false} />);

    expect(removeSpy).toHaveBeenCalledWith('resize', expect.any(Function));
    removeSpy.mockRestore();
  });
});
