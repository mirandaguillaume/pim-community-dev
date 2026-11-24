import {escapeHtml} from '@src/shared/escape-html';

describe('escapeHtml', () => {
    it('escapes the characters that open a tag or close an attribute', () => {
        expect(escapeHtml(`<img src=x onerror="alert('x')">&`)).toBe(
            '&lt;img src=x onerror=&quot;alert(&#39;x&#39;)&quot;&gt;&amp;'
        );
    });

    it('leaves a plain username untouched', () => {
        expect(escapeHtml('magento_0123')).toBe('magento_0123');
    });
});
