import {debounceCallback} from '@src/shared/utils/debounce-callback';

describe('debounceCallback', () => {
    beforeEach(() => {
        jest.useFakeTimers();
    });

    afterEach(() => {
        jest.useRealTimers();
    });

    it('calls the callback after the delay', () => {
        const fn = jest.fn();
        const debounced = debounceCallback(fn, 200);

        debounced();
        expect(fn).not.toHaveBeenCalled();

        jest.advanceTimersByTime(200);
        expect(fn).toHaveBeenCalledTimes(1);
    });

    it('only fires once when called multiple times within the delay', () => {
        const fn = jest.fn();
        const debounced = debounceCallback(fn, 200);

        debounced();
        debounced();
        debounced();

        jest.advanceTimersByTime(200);
        expect(fn).toHaveBeenCalledTimes(1);
    });

    it('forwards arguments to the callback', () => {
        const fn = jest.fn();
        const debounced = debounceCallback(fn, 100);

        debounced('a', 42);
        jest.advanceTimersByTime(100);

        expect(fn).toHaveBeenCalledWith('a', 42);
    });

    it('does not fire if cancelled before the delay', () => {
        const fn = jest.fn();
        const debounced = debounceCallback(fn, 300);

        debounced();
        jest.advanceTimersByTime(100);
        debounced();
        jest.advanceTimersByTime(100);

        expect(fn).not.toHaveBeenCalled();
    });
});
