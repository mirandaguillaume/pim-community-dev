import {sanitize} from '@src/shared/sanitize';

describe('sanitize', () => {
    it('lowercases the value', () => {
        expect(sanitize('HELLO')).toBe('hello');
    });

    it('replaces spaces with underscores', () => {
        expect(sanitize('hello world')).toBe('helloworld');
    });

    it('removes spaces entirely (not replacing with underscore)', () => {
        expect(sanitize('a b')).toBe('ab');
    });

    it('replaces special characters with underscores', () => {
        expect(sanitize('hello-world')).toBe('hello_world');
        expect(sanitize('hello.world')).toBe('hello_world');
        expect(sanitize('héllo')).toBe('h_llo');
    });

    it('keeps alphanumeric characters and underscores unchanged', () => {
        expect(sanitize('hello_world_123')).toBe('hello_world_123');
    });

    it('returns empty string for empty input', () => {
        expect(sanitize('')).toBe('');
    });

    it('handles a realistic connection code', () => {
        expect(sanitize('My Connection')).toBe('myconnection');
    });
});
