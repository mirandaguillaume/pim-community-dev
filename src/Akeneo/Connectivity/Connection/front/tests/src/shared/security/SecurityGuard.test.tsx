import React from 'react';
import {render, screen} from '@testing-library/react';
import {SecurityGuard} from '@src/shared/security/SecurityGuard';
import {SecurityContext} from '@src/shared/security/security-context';

describe('SecurityGuard', () => {
    it('renders children when the ACL is granted', () => {
        render(
            <SecurityContext.Provider value={{isGranted: () => true}}>
                <SecurityGuard acl='pim_connectivity_connection_manage'>
                    <span>Protected content</span>
                </SecurityGuard>
            </SecurityContext.Provider>
        );

        expect(screen.getByText('Protected content')).toBeInTheDocument();
    });

    it('renders the fallback when the ACL is not granted', () => {
        render(
            <SecurityContext.Provider value={{isGranted: () => false}}>
                <SecurityGuard acl='pim_connectivity_connection_manage' fallback={<span>Access denied</span>}>
                    <span>Protected content</span>
                </SecurityGuard>
            </SecurityContext.Provider>
        );

        expect(screen.queryByText('Protected content')).not.toBeInTheDocument();
        expect(screen.getByText('Access denied')).toBeInTheDocument();
    });

    it('renders nothing when the ACL is not granted and no fallback is provided', () => {
        const {container} = render(
            <SecurityContext.Provider value={{isGranted: () => false}}>
                <SecurityGuard acl='pim_connectivity_connection_manage'>
                    <span>Protected content</span>
                </SecurityGuard>
            </SecurityContext.Provider>
        );

        expect(container).toBeEmptyDOMElement();
    });
});
