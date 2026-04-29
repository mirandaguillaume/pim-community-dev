import {getScrollPosition} from '@src/shared/scroll/utils/getScrollPosition';

describe('getScrollPosition', () => {
    it('extracts scrollTop, clientHeight and scrollHeight from element', () => {
        const element = {scrollTop: 100, clientHeight: 400, scrollHeight: 1200} as Element;
        expect(getScrollPosition(element)).toStrictEqual({
            scrollTop: 100,
            clientHeight: 400,
            scrollHeight: 1200,
        });
    });

    it('returns zeros when element is at top with no overflow', () => {
        const element = {scrollTop: 0, clientHeight: 500, scrollHeight: 500} as Element;
        expect(getScrollPosition(element)).toStrictEqual({
            scrollTop: 0,
            clientHeight: 500,
            scrollHeight: 500,
        });
    });

    it('does not include extra element properties in the result', () => {
        const element = {scrollTop: 10, clientHeight: 200, scrollHeight: 800, id: 'extra'} as unknown as Element;
        const result = getScrollPosition(element);
        expect(Object.keys(result)).toHaveLength(3);
        expect(result).not.toHaveProperty('id');
    });
});
