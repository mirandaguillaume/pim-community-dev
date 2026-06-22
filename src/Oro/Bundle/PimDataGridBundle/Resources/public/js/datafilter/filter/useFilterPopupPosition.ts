import {RefObject, useLayoutEffect} from 'react';

type Rect = {left: number; top: number; width: number; height: number};
type Viewport = {width: number; height: number};

/**
 * Flip/clamp positioning math for a datagrid filter criteria popup, lifted verbatim from the legacy
 * `AbstractFilter._updateCriteriaSelectorPosition`.
 *
 * The grid filter panel uses `overflow-x`/`overflow-y` with different values, which clips
 * `position:absolute` descendants. The historical fix is to render the popup `position:fixed` and
 * manually place it aligned with its filter: keep it at the anchor's top-left, but flip it left when
 * it would overflow the right edge (and there is room), and flip it up when it would overflow the
 * bottom (clamping to 0 when there is no room either way).
 *
 * Kept as a pure function (no DOM, no React) so the branch math is unit-tested directly.
 */
export const computeFilterPopupPosition = (
  anchor: Rect,
  popup: Rect,
  viewport: Viewport
): {left: number; top: number} => {
  const expectedLeft = anchor.left;
  const left =
    expectedLeft + popup.width > viewport.width && expectedLeft + anchor.width - popup.width > 0
      ? expectedLeft + anchor.width - popup.width
      : expectedLeft;

  const expectedTop = anchor.top;
  let top: number;
  if (expectedTop + popup.height <= viewport.height) {
    top = expectedTop;
  } else if (expectedTop + anchor.height - popup.height > 0) {
    top = expectedTop + anchor.height - popup.height;
  } else {
    top = 0;
  }

  return {left, top};
};

/**
 * Position a filter criteria popup `position:fixed` aligned with its anchor (the filter root, i.e.
 * the popup's parent element — faithful to the legacy `this.$el.offset()`), re-positioning while the
 * popup is open as the filter panel scrolls or the window resizes.
 *
 * The React counterpart of the legacy `_updateCriteriaSelectorPosition` + its `.column-inner` scroll
 * binding. Writes `position`/`left`/`top` IMPERATIVELY through the ref — the popup renders no `style`
 * prop, so React never reconciles these writes away, exactly as the jQuery `.show()`/`.hide()` (which
 * still own the `display` toggle) coexist with it. `useLayoutEffect` mirrors the legacy synchronous
 * positioning (no first-paint flicker at a default position).
 */
export const useFilterPopupPosition = (popupRef: RefObject<HTMLElement>, isOpen: boolean): void => {
  useLayoutEffect(() => {
    const popup = popupRef.current;
    const anchor = popup?.parentElement ?? null;
    if (!isOpen || popup === null || anchor === null) {
      return;
    }

    const reposition = (): void => {
      popup.style.position = 'fixed';
      const {left, top} = computeFilterPopupPosition(anchor.getBoundingClientRect(), popup.getBoundingClientRect(), {
        width: document.body.clientWidth,
        height: document.body.clientHeight,
      });
      popup.style.left = `${left}px`;
      popup.style.top = `${top}px`;
    };

    reposition();

    const columnInner = anchor.closest('.column-inner');
    columnInner?.addEventListener('scroll', reposition);
    window.addEventListener('resize', reposition);

    return () => {
      columnInner?.removeEventListener('scroll', reposition);
      window.removeEventListener('resize', reposition);
    };
  }, [popupRef, isOpen]);
};
