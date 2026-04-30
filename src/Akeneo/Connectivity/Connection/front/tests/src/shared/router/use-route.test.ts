import {useRoute} from '@src/shared/router/use-route';
import {RouterContext} from '@src/shared/router/router-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const generate = jest.fn((route: string, params?: {[k: string]: string}) =>
    params ? route + '?' + new URLSearchParams(params).toString() : route
);

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(RouterContext.Provider, {value: {generate, redirect: jest.fn()}}, children);

beforeEach(() => generate.mockClear());

describe('useRoute', () => {
    it('calls generate with the route name and returns the URL', () => {
        const {result} = renderHook(() => useRoute('my_route'), {wrapper});
        expect(result.current).toBe('my_route');
        expect(generate).toHaveBeenCalledWith('my_route', undefined);
    });

    it('passes parameters to generate', () => {
        const {result} = renderHook(() => useRoute('akeneo_rest_get', {code: 'magento'}), {wrapper});
        expect(result.current).toBe('akeneo_rest_get?code=magento');
        expect(generate).toHaveBeenCalledWith('akeneo_rest_get', {code: 'magento'});
    });

    it('returns the same value on re-render when inputs are stable', () => {
        const {result, rerender} = renderHook(() => useRoute('stable_route'), {wrapper});
        const first = result.current;
        rerender();
        expect(result.current).toBe(first);
        expect(generate).toHaveBeenCalledTimes(1);
    });
});
