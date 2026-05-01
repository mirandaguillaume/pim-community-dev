import React from 'react';
import {messageWithColoredParameters} from '@src/error-management/message-with-colored-parameters';
import {renderWithProviders} from '../../test-utils';

describe('messageWithColoredParameters', () => {
    it('replaces {key} placeholders for domain_error type', () => {
        const result = messageWithColoredParameters('Value {value} is invalid', {value: 'foo'}, 'domain_error');

        const {container} = renderWithProviders(<>{result}</>);
        expect(container.textContent).toBe('Value foo is invalid');
    });

    it('replaces key literal for non-domain type', () => {
        const result = messageWithColoredParameters('Value value is invalid', {value: 'foo'}, 'technical_error');

        const {container} = renderWithProviders(<>{result}</>);
        expect(container.textContent).toBe('Value foo is invalid');
    });

    it('handles multiple parameters', () => {
        const result = messageWithColoredParameters('{a} and {b}', {a: 'hello', b: 'world'}, 'domain_error');

        const {container} = renderWithProviders(<>{result}</>);
        expect(container.textContent).toBe('hello and world');
    });

    it('returns the original template when no parameter matches', () => {
        const result = messageWithColoredParameters('No placeholders', {foo: 'bar'}, 'domain_error');

        const {container} = renderWithProviders(<>{result}</>);
        expect(container.textContent).toBe('No placeholders');
    });
});
