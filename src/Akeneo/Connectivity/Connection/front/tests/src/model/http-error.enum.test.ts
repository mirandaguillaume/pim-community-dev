import {HttpError} from '@src/model/http-error.enum';

describe('HttpError', () => {
    describe('string values', () => {
        it("NotFound is 'NOT_FOUND'", () => expect(HttpError.NotFound).toBe('NOT_FOUND'));
        it("Forbidden is 'FORBIDDEN'", () => expect(HttpError.Forbidden).toBe('FORBIDDEN'));
    });

    it('has exactly 2 members', () => {
        expect(Object.keys(HttpError)).toHaveLength(2);
    });
});
