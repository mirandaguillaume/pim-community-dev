import React from 'react';
import {useNavigate} from 'react-router-dom';
import {Modal} from '../../common';
import {Translate} from '../../shared/translate';
import {ConnectionCreateForm} from '../components/ConnectionCreateForm';

export const CreateConnection = () => {
    const navigate = useNavigate();

    return (
        <Modal
            subTitle={<Translate id='akeneo_connectivity.connection.connections' />}
            title={<Translate id='akeneo_connectivity.connection.create_connection.title' />}
            description={<Translate id='akeneo_connectivity.connection.create_connection.description' />}
            onCancel={() => navigate('/connect/connection-settings')}
        >
            <ConnectionCreateForm />
        </Modal>
    );
};
