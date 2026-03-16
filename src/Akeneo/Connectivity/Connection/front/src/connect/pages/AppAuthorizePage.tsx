import React, {FC} from 'react';
import {useLocation, useNavigate} from 'react-router-dom';
import {AuthorizeClientError} from '../components/AuthorizeClientError';
import {AppWizard} from '../components/AppWizard/AppWizard';

export const AppAuthorizePage: FC = () => {
    const navigate = useNavigate();
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const error = query.get('error');
    const clientId = query.get('client_id');

    if (null !== error) {
        return <AuthorizeClientError error={error} />;
    }

    if (null === clientId) {
        navigate('/connect/app-store');
        return null;
    }

    return <AppWizard clientId={clientId} />;
};
