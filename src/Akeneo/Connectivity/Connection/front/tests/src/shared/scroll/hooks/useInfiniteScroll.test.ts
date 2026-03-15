import {renderHook, act} from '@testing-library/react';
import useInfiniteScroll from '@src/shared/scroll/hooks/useInfiniteScroll';
import {fireEvent} from '@testing-library/dom';

beforeEach(() => {
    document.body.innerHTML = `
    <div>
        <div id='content'></div>
    </div>
    `;
});

test('The first page is fetched on mount', async () => {
    const ref = {
        current: document.getElementById('content'),
    };

    const loadNextPage = jest.fn().mockImplementationOnce(() => {
        return Promise.resolve(null);
    });
    const {unmount} = renderHook(() => useInfiniteScroll(loadNextPage, ref));

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });
    expect(loadNextPage).toHaveBeenCalledTimes(1);

    unmount();
});

test('The second page is fetched, with the first response as parameter', async () => {
    const ref = {
        current: document.getElementById('content'),
    };

    const loadNextPage = jest
        .fn()
        .mockImplementationOnce(() => {
            return Promise.resolve({
                results: ['foo', 'bar'],
                search_after: 'bar',
            });
        })
        .mockImplementationOnce(prev => {
            expect(prev).toEqual({
                results: ['foo', 'bar'],
                search_after: 'bar',
            });
            return Promise.resolve(null);
        });

    const {unmount} = renderHook(() => useInfiniteScroll(loadNextPage, ref));

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });
    expect(loadNextPage).toHaveBeenCalledTimes(1);

    // Since we are in jest, the scroll is not computed correctly.
    // Meaning is, since the scroll height is stuck at 0, it will try anyway.
    // The useScrollPosition hook debounces by 100ms, so we must wait longer than that.
    fireEvent.scroll(document.body, {target: {scrollY: 100}});

    await act(async () => {
        await new Promise(r => setTimeout(r, 150));
    });
    expect(loadNextPage).toHaveBeenCalledTimes(2);

    unmount();
});

test('Reset the scroll should fetch the first page', async () => {
    const ref = {
        current: document.getElementById('content'),
    };

    const loadNextPage = jest.fn().mockImplementation(() => {
        return Promise.resolve(null);
    });

    const {result, unmount} = renderHook(() => useInfiniteScroll(loadNextPage, ref));

    expect(loadNextPage).toHaveBeenCalledTimes(1);
    expect(result.current.isLoading).toBeTruthy();

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current.isLoading).toBeFalsy();

    act(() => {
        result.current.reset();
    });

    expect(loadNextPage).toHaveBeenCalledTimes(2);

    unmount();
});

test('The hook does not render more times than necessary', async () => {
    const ref = {
        current: document.getElementById('content'),
    };

    const loadNextPage = jest.fn().mockImplementation(() => {
        return Promise.resolve(null);
    });

    let renderCount = 0;

    const {unmount} = renderHook(() => {
        renderCount++;
        useInfiniteScroll(loadNextPage, ref);
    });

    expect(renderCount).toBe(1);
    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });
    expect(renderCount).toBe(2);

    unmount();
});
