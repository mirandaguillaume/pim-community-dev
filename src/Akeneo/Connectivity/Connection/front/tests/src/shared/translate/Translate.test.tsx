import React from 'react';
import '@testing-library/jest-dom';
import {render, screen} from '@testing-library/react';
import {Translate} from '@src/shared/translate/Translate';
import {TranslateContext} from '@src/shared/translate/translate-context';

const translate = jest.fn((id: string) => `translated:${id}`);

describe('Translate', () => {
    afterEach(() => {
        jest.clearAllMocks();
    });

    it('renders the translated string for a given id', () => {
        render(
            <TranslateContext.Provider value={translate}>
                <Translate id='akeneo_connectivity.connection.label' />
            </TranslateContext.Provider>
        );

        expect(screen.getByText('translated:akeneo_connectivity.connection.label')).toBeInTheDocument();
    });

    it('forwards placeholders and count to the translate function', () => {
        render(
            <TranslateContext.Provider value={translate}>
                <Translate id='some.key' placeholders={{name: 'Akeneo'}} count={3} />
            </TranslateContext.Provider>
        );

        expect(translate).toHaveBeenCalledWith('some.key', {name: 'Akeneo'}, 3);
    });
});
