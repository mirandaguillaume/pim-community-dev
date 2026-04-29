import {BadRequestError} from '@src/shared/fetch/errors/bad-request-error';

describe('BadRequestError', () => {
    it('is an instance of Error', () => {
        expect(new BadRequestError()).toBeInstanceOf(Error);
    });

    it('is an instance of BadRequestError', () => {
        expect(new BadRequestError()).toBeInstanceOf(BadRequestError);
    });

    it('stores data when provided', () => {
        const data = {violations: [{message: 'Field required'}]};
        const error = new BadRequestError(data);
        expect(error.data).toStrictEqual(data);
    });

    it('has undefined data when not provided', () => {
        const error = new BadRequestError();
        expect(error.data).toBeUndefined();
    });

    it('preserves instanceof across serialization boundary (prototype chain fix)', () => {
        const error = new BadRequestError();
        expect(Object.getPrototypeOf(error)).toBe(BadRequestError.prototype);
    });
});
