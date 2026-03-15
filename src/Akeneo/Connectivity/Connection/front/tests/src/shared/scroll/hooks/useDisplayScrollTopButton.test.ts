import {renderHook, act} from '@testing-library/react';
import {useDisplayScrollTopButton} from '@src/shared/scroll/hooks/useDisplayScrollTopButton';
import {fireEvent} from '@testing-library/dom';
import {ScrollPosition} from '@src/shared/scroll/utils/getScrollPosition';

jest.mock('@src/shared/scroll/utils/getScrollPosition', () => ({
    getScrollPosition: (element: Element): ScrollPosition => {
        return {
            scrollTop: 1000,
            clientHeight: 0,
            scrollHeight: 0,
        };
    },
}));

beforeEach(() => {
    document.body.innerHTML = `
    <div>
        <div id='content'></div>
    </div>
    `;
});

test('Display scroll top button after scrolling', async () => {
    const ref = {
        current: document.getElementById('content'),
    };

    const {result, unmount} = renderHook(() => useDisplayScrollTopButton(ref));

    expect(result.current).toBe(false);

    fireEvent.scroll(window, {target: {scrollY: 500}});

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current).toBe(true);

    unmount();
});
