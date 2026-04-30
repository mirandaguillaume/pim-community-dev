import {copyTextToClipboard} from '@src/settings/copy-text-to-clipboard';

describe('copyTextToClipboard', () => {
    it('does nothing when getSelection returns null', () => {
        jest.spyOn(window, 'getSelection').mockReturnValue(null);

        expect(() => copyTextToClipboard(document.createElement('span'))).not.toThrow();
    });

    it('selects the element contents and copies them', () => {
        const mockRange = {selectNodeContents: jest.fn()} as any;
        const mockSelection = {removeAllRanges: jest.fn(), addRange: jest.fn()} as any;

        jest.spyOn(window, 'getSelection').mockReturnValue(mockSelection);
        jest.spyOn(document, 'createRange').mockReturnValue(mockRange);
        jest.spyOn(document, 'execCommand').mockReturnValue(true);

        const el = document.createElement('span');
        copyTextToClipboard(el);

        expect(mockRange.selectNodeContents).toHaveBeenCalledWith(el);
        expect(mockSelection.addRange).toHaveBeenCalledWith(mockRange);
        expect(document.execCommand).toHaveBeenCalledWith('copy');
        expect(mockSelection.removeAllRanges).toHaveBeenCalledTimes(2);
    });
});
