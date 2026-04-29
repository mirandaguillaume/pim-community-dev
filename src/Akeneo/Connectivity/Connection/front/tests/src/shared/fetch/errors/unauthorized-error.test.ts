import {UnauthorizedError} from '@src/shared/fetch/errors/unauthorized-error';

describe('UnauthorizedError', () => {
    it('is an instance of Error', () => {
        expect(new UnauthorizedError()).toBeInstanceOf(Error);
    });

    it('is an instance of UnauthorizedError', () => {
        expect(new UnauthorizedError()).toBeInstanceOf(UnauthorizedError);
    });

    it('preserves instanceof across serialization boundary (prototype chain fix)', () => {
        const error = new UnauthorizedError();
        expect(Object.getPrototypeOf(error)).toBe(UnauthorizedError.prototype);
    });
});
