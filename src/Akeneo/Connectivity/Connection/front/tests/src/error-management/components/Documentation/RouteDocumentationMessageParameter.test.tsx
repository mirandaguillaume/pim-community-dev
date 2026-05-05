import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {RouteDocumentationMessageParameter} from '@src/error-management/components/Documentation/RouteDocumentationMessageParameter';
import {RouterContext} from '@src/shared/router';
import {RouteParameter, RouteType} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

const routeParam: RouteParameter = {
    type: RouteType,
    title: 'See the attribute',
    route: 'pim_catalog_attribute_index',
    routeParameters: {},
};

describe('RouteDocumentationMessageParameter', () => {
    it('renders the route parameter title', () => {
        renderWithProviders(<RouteDocumentationMessageParameter routeParam={routeParam} />);

        expect(screen.getByText('See the attribute')).toBeInTheDocument();
    });

    it('calls router.redirect when clicked', () => {
        const redirect = jest.fn();
        const generate = jest.fn().mockReturnValue('/akeneo/attribute');

        renderWithProviders(
            <RouterContext.Provider value={{redirect, generate}}>
                <RouteDocumentationMessageParameter routeParam={routeParam} />
            </RouterContext.Provider>
        );

        userEvent.click(screen.getByText('See the attribute'));

        expect(redirect).toHaveBeenCalledWith('/akeneo/attribute');
        expect(generate).toHaveBeenCalledWith('pim_catalog_attribute_index', {});
    });
});
