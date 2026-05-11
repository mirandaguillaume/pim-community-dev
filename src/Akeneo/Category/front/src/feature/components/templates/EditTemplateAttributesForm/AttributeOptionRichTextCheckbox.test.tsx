import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {SaveStatusContext, Status} from '../../providers/SaveStatusProvider';
import {AttributeOptionRichTextCheckbox} from './AttributeOptionRichTextCheckbox';
import {Attribute} from '../../../models';

const baseAttribute: Attribute = {
  uuid: 'attr-uuid',
  code: 'description',
  type: 'textarea',
  order: 0,
  is_scopable: false,
  is_localizable: true,
  labels: {en_US: 'Description'},
  template_uuid: 'tmpl-uuid',
};

const renderWithFullProviders = (ui: React.ReactElement) => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <SaveStatusContext.Provider value={{globalStatus: Status.SAVED, handleStatusListChange: jest.fn()}}>
        {ui}
      </SaveStatusContext.Provider>
    </QueryClientProvider>
  );
};

describe('AttributeOptionRichTextCheckbox', () => {
  it('renders the rich text label for textarea type', () => {
    renderWithFullProviders(<AttributeOptionRichTextCheckbox attribute={baseAttribute} />);
    expect(
      screen.getByText('akeneo.category.template.attribute.settings.options.rich_text')
    ).toBeInTheDocument();
  });

  it('renders the rich text label for richtext type', () => {
    renderWithFullProviders(
      <AttributeOptionRichTextCheckbox attribute={{...baseAttribute, type: 'richtext'}} />
    );
    expect(
      screen.getByText('akeneo.category.template.attribute.settings.options.rich_text')
    ).toBeInTheDocument();
  });

  it('renders nothing for text type', () => {
    const {container} = renderWithFullProviders(
      <AttributeOptionRichTextCheckbox attribute={{...baseAttribute, type: 'text'}} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders nothing for image type', () => {
    const {container} = renderWithFullProviders(
      <AttributeOptionRichTextCheckbox attribute={{...baseAttribute, type: 'image'}} />
    );
    expect(container.firstChild).toBeNull();
  });
});
