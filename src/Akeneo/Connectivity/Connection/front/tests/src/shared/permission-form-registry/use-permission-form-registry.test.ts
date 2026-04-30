import {usePermissionFormRegistry} from '@src/shared/permission-form-registry/use-permission-form-registry';
import {PermissionFormRegistryContext} from '@src/shared/permission-form-registry/permission-form-registry-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const allProviders = jest.fn(() => Promise.resolve([]));

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(PermissionFormRegistryContext.Provider, {value: {all: allProviders}}, children);

describe('usePermissionFormRegistry', () => {
    it('returns the registry from context', () => {
        const {result} = renderHook(() => usePermissionFormRegistry(), {wrapper});
        expect(result.current.all).toBe(allProviders);
    });

    it('all() delegates to the context provider', async () => {
        const {result} = renderHook(() => usePermissionFormRegistry(), {wrapper});
        const providers = await result.current.all();
        expect(providers).toStrictEqual([]);
        expect(allProviders).toHaveBeenCalledTimes(1);
    });

    it('context default all() resolves to empty array', async () => {
        const {result} = renderHook(() => usePermissionFormRegistry());
        const providers = await result.current.all();
        expect(providers).toStrictEqual([]);
    });
});
