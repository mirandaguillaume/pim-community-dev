import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ConnectionSelect} from '@src/audit/components/ConnectionSelect';
import {Connection} from '@src/model/connection';
import {FlowType} from '@src/model/flow-type.enum';
import {renderWithProviders} from '../../../test-utils';

const baseConnection: Connection = {
    code: 'erp',
    label: 'ERP Connection',
    flowType: FlowType.DATA_SOURCE,
    image: null,
    auditable: true,
};

describe('ConnectionSelect', () => {
    it('renders the label prop', () => {
        renderWithProviders(<ConnectionSelect connections={[]} onChange={jest.fn()} label='Select a connection' />);

        expect(screen.getByText('Select a connection')).toBeInTheDocument();
    });

    it('renders nothing for Select when connections list is empty', () => {
        const {container} = renderWithProviders(
            <ConnectionSelect connections={[]} onChange={jest.fn()} label='Label' />
        );

        expect(container.querySelector('button')).not.toBeInTheDocument();
    });

    it('renders the first connection label in the selector', () => {
        renderWithProviders(<ConnectionSelect connections={[baseConnection]} onChange={jest.fn()} label='Label' />);

        expect(screen.getByText('ERP Connection')).toBeInTheDocument();
    });

    it('calls onChange with the first connection code on mount', () => {
        const onChange = jest.fn();
        renderWithProviders(<ConnectionSelect connections={[baseConnection]} onChange={onChange} label='Label' />);

        expect(onChange).toHaveBeenCalledWith('erp');
    });

    it('renders a second connection in the selector when it is first in the list', () => {
        const second: Connection = {...baseConnection, code: 'crm', label: 'CRM Connection'};
        renderWithProviders(
            <ConnectionSelect connections={[second, baseConnection]} onChange={jest.fn()} label='Label' />
        );

        expect(screen.getByText('CRM Connection')).toBeInTheDocument();
    });
});
