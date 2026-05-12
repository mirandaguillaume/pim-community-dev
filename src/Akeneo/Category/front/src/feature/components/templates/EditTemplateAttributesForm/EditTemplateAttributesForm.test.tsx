import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EditTemplateAttributesForm} from './EditTemplateAttributesForm';
import {Attribute} from '../../../models';

jest.mock('./InitializeTemplateChoice', () => ({
  InitializeTemplateChoice: () => <div data-testid="initialize-choice" />,
}));

jest.mock('./AttributeList', () => ({
  AttributeList: ({onAttributeSelection, attributes}: any) => (
    <div data-testid="attribute-list">
      {attributes.map((a: Attribute) => (
        <button key={a.uuid} onClick={() => onAttributeSelection(a)}>
          {a.code}
        </button>
      ))}
    </div>
  ),
}));

jest.mock('./AttributeSettings', () => ({
  AttributeSettings: ({attribute}: any) => <div data-testid={`settings-${attribute.uuid}`} />,
}));

const makeAttribute = (uuid: string, code: string): Attribute => ({
  uuid,
  code,
  type: 'text',
  order: 0,
  is_scopable: false,
  is_localizable: false,
  labels: {},
  template_uuid: 'tmpl-uuid',
});

describe('EditTemplateAttributesForm', () => {
  it('renders InitializeTemplateChoice when there are no attributes', () => {
    renderWithProviders(<EditTemplateAttributesForm attributes={[]} templateId="tmpl-uuid" />);
    expect(screen.getByTestId('initialize-choice')).toBeInTheDocument();
  });

  it('renders AttributeList and settings for the first attribute by default', () => {
    const attrs = [makeAttribute('uuid-1', 'name'), makeAttribute('uuid-2', 'desc')];
    renderWithProviders(<EditTemplateAttributesForm attributes={attrs} templateId="tmpl-uuid" />);
    expect(screen.getByTestId('attribute-list')).toBeInTheDocument();
    expect(screen.getByTestId('settings-uuid-1')).toBeInTheDocument();
  });

  it('switches to the settings of the selected attribute when clicked', async () => {
    const attrs = [makeAttribute('uuid-1', 'name'), makeAttribute('uuid-2', 'desc')];
    renderWithProviders(<EditTemplateAttributesForm attributes={attrs} templateId="tmpl-uuid" />);
    await userEvent.click(screen.getByRole('button', {name: 'desc'}));
    expect(screen.getByTestId('settings-uuid-2')).toBeInTheDocument();
  });
});
