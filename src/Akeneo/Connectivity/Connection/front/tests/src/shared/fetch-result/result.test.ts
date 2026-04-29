import {ok, err, isOk, isErr} from '@src/shared/fetch-result/result';

describe('ok', () => {
    it('wraps a value in an Ok result', () => {
        expect(ok('hello')).toStrictEqual({value: 'hello'});
    });

    it('works with falsy values', () => {
        expect(ok(null)).toStrictEqual({value: null});
        expect(ok(0)).toStrictEqual({value: 0});
        expect(ok(false)).toStrictEqual({value: false});
    });
});

describe('err', () => {
    it('wraps an error in an Err result', () => {
        expect(err('something went wrong')).toStrictEqual({error: 'something went wrong'});
    });

    it('works with object errors', () => {
        const errorData = {code: 400, message: 'Bad request'};
        expect(err(errorData)).toStrictEqual({error: errorData});
    });
});

describe('isOk', () => {
    it('returns true for an Ok result', () => {
        expect(isOk(ok('value'))).toBe(true);
    });

    it('returns false for an Err result', () => {
        expect(isOk(err('error'))).toBe(false);
    });
});

describe('isErr', () => {
    it('returns true for an Err result', () => {
        expect(isErr(err('error'))).toBe(true);
    });

    it('returns false for an Ok result', () => {
        expect(isErr(ok('value'))).toBe(false);
    });
});
