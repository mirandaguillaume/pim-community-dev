import {useSecurity} from '@src/shared/security/use-security';
import {SecurityContext} from '@src/shared/security/security-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const isGranted = jest.fn((acl: string) => acl === 'allowed_acl');

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(SecurityContext.Provider, {value: {isGranted}}, children);

describe('useSecurity', () => {
    it('returns the security object from SecurityContext', () => {
        const {result} = renderHook(() => useSecurity(), {wrapper});
        expect(result.current.isGranted).toBe(isGranted);
    });

    it('isGranted returns true for allowed ACL', () => {
        const {result} = renderHook(() => useSecurity(), {wrapper});
        expect(result.current.isGranted('allowed_acl')).toBe(true);
    });

    it('isGranted returns false for denied ACL', () => {
        const {result} = renderHook(() => useSecurity(), {wrapper});
        expect(result.current.isGranted('denied_acl')).toBe(false);
    });
});
