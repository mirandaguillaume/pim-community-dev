import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Button} from '@src/common/components/button/Button';
import {renderWithProviders} from '../../../../test-utils';

describe('Button', () => {
    it('renders children', () => {
        renderWithProviders(<Button>Click me</Button>);

        expect(screen.getByRole('button', {name: 'Click me'})).toBeInTheDocument();
    });

    it('always has the AknButton class', () => {
        renderWithProviders(<Button className=''>Save</Button>);

        expect(screen.getByRole('button')).toHaveClass('AknButton');
    });

    it('adds AknButton--disabled class when disabled', () => {
        renderWithProviders(
            <Button className='' disabled>
                Save
            </Button>
        );

        expect(screen.getByRole('button')).toHaveClass('AknButton--disabled');
        expect(screen.getByRole('button')).toBeDisabled();
    });

    it('renders a count badge when count is provided', () => {
        const {container} = renderWithProviders(
            <Button className='' count={5}>
                Items
            </Button>
        );

        expect(container.querySelector('.AknButton--withSuffix')).toHaveTextContent('5');
    });

    it('calls onClick when clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(
            <Button className='' onClick={onClick}>
                Click
            </Button>
        );

        userEvent.click(screen.getByRole('button'));

        expect(onClick).toHaveBeenCalledTimes(1);
    });
});
