import React, {FC} from 'react';
import {useLocation, useNavigate} from 'react-router-dom';
import {AuthenticationModal} from '../components/AppWizard/AuthenticationModal';

const useQuery = () => {
    const {search} = useLocation();

    return React.useMemo(() => new URLSearchParams(search), [search]);
};

export const AppAuthenticatePage: FC = () => {
    const navigate = useNavigate();
    const query = useQuery();

    const clientId = query.get('client_id');

    if (!clientId) {
        navigate('/connect/app-store');
        return null;
    }

    return <AuthenticationModal clientId={clientId} />;
};
