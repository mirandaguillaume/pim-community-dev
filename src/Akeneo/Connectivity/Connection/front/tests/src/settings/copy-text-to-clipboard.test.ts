import {copyTextToClipboard} from '@src/settings/copy-text-to-clipboard';

describe('copyTextToClipboard', () => {
    afterEach(() => {
        jest.restoreAllMocks();
    });

    it('selects element content and executes copy command', () => {
        const range = {selectNodeContents: jest.fn()} as unknown as Range;
        const selection = {
            removeAllRanges: jest.fn(),
            addRange: jest.fn(),
        } as unknown as Selection;

        jest.spyOn(document, 'createRange').mockReturnValue(range);
        jest.spyOn(window, 'getSelection').mockReturnValue(selection);

        const execCommandMock = jest.fn().mockReturnValue(true);
        Object.defineProperty(document, 'execCommand', {
            value: execCommandMock,
            writable: true,
            configurable: true,
        });

        const element = document.createElement('div');
        element.textContent = 'copy me';

        copyTextToClipboard(element);

        expect(range.selectNodeContents).toHaveBeenCalledWith(element);
        expect(selection.removeAllRanges).toHaveBeenCalledTimes(2);
        expect(selection.addRange).toHaveBeenCalledWith(range);
        expect(execCommandMock).toHaveBeenCalledWith('copy');
    });

    it('does nothing when getSelection returns null', () => {
        jest.spyOn(window, 'getSelection').mockReturnValue(null);
        const createRangeSpy = jest.spyOn(document, 'createRange');

        copyTextToClipboard(document.createElement('div'));

        expect(createRangeSpy).not.toHaveBeenCalled();
    });
});
