import React, {FC, useCallback, useState} from 'react';
import {useNavigate} from 'react-router-dom';
import {AppIllustration, getColor, getFontSize, Modal} from 'akeneo-design-system';
import styled from '../../common/styled-with-theme';
import {useTranslate} from '../../shared/translate';
import {useRouter} from '../../shared/router/use-router';
import {CreateCustomAppForm} from '../components/CustomApps/CreateCustomAppForm';
import {CustomAppCredentials} from '../../model/Apps/custom-app-credentials';
import {CreateCustomAppCredentials} from '../components/CustomApps/CreateCustomAppCredentials';

const Subtitle = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;

export const CreateCustomAppPage: FC = () => {
    const navigate = useNavigate();
    const generateUrl = useRouter();
    const translate = useTranslate();
    const [credentials, setCredentials] = useState<CustomAppCredentials | null>(null);

    const handleCloseModal = useCallback(() => {
        navigate(generateUrl('akeneo_connectivity_connection_connect_marketplace'));
    }, [navigate, generateUrl]);

    return (
        <Modal
            onClose={handleCloseModal}
            illustration={<AppIllustration />}
            closeTitle={translate('pim_common.cancel')}
        >
            <Subtitle>{translate('akeneo_connectivity.connection.connect.custom_apps.create_modal.subtitle')}</Subtitle>
            {null === credentials && (
                <CreateCustomAppForm onCancel={handleCloseModal} setCredentials={setCredentials} />
            )}
            {null !== credentials && (
                <CreateCustomAppCredentials onClose={handleCloseModal} credentials={credentials} />
            )}
        </Modal>
    );
};
