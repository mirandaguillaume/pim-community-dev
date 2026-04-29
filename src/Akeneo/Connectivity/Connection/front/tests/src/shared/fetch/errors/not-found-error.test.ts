import {NotFoundError} from '@src/shared/fetch/errors/not-found-error';

describe('NotFoundError', () => {
    it('is an instance of Error', () => {
        expect(new NotFoundError()).toBeInstanceOf(Error);
    });

    it('is an instance of NotFoundError', () => {
        expect(new NotFoundError()).toBeInstanceOf(NotFoundError);
    });

    it('preserves instanceof across serialization boundary (prototype chain fix)', () => {
        const error = new NotFoundError();
        expect(Object.getPrototypeOf(error)).toBe(NotFoundError.prototype);
    });
});
