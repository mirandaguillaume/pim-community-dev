import {useRouter} from '@src/shared/router/use-router';
import {RouterContext} from '@src/shared/router/router-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const generate = jest.fn();

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(RouterContext.Provider, {value: {generate, redirect: jest.fn()}}, children);

describe('useRouter', () => {
    it('returns the generate function from RouterContext', () => {
        const {result} = renderHook(() => useRouter(), {wrapper});
        expect(result.current).toBe(generate);
    });

    it('returned function delegates to the context generate', () => {
        generate.mockReturnValueOnce('my_route?foo=bar');
        const {result} = renderHook(() => useRouter(), {wrapper});
        const url = result.current('my_route', {foo: 'bar'});
        expect(url).toBe('my_route?foo=bar');
        expect(generate).toHaveBeenCalledWith('my_route', {foo: 'bar'});
    });
});
