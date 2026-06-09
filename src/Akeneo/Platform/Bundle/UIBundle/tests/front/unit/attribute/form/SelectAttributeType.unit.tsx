import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

const mockUseGetIdentifierAttributesCount = jest.fn(() => ({count: 0}));

jest.mock('../../../../../Resources/public/js/attribute/form/hooks', () => ({
  useGetIdentifierAttributesCount: () => mockUseGetIdentifierAttributesCount(),
  useMainIdentifierCode: () => 'sku',
}));

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useFeatureFlags: () => ({isEnabled: () => true}),
  useRouter: () => ({generate: (url: string) => url}),
}));

import SelectAttributeType from '../../../../../Resources/public/js/attribute/form/SelectAttributeType';

afterEach(() => {
  global.fetch && (global.fetch as jest.Mock).mockClear();
  delete global.fetch;
  mockUseGetIdentifierAttributesCount.mockReturnValue({count: 0});
});

test('It renders attribute type tiles after fetching', async () => {
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_text: {}, pim_catalog_textarea: {}}),
  });

  renderWithProviders(<SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={jest.fn()} />);

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_text');
  expect(screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_textarea')).toBeInTheDocument();
});

test('It calls onStepConfirm with the chosen attribute type', async () => {
  const onStepConfirm = jest.fn();
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_text: {}}),
  });

  renderWithProviders(<SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={onStepConfirm} />);

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_text');
  userEvent.click(screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_text'));
  expect(onStepConfirm).toHaveBeenCalledWith({attribute_type: 'pim_catalog_text'});
});

test('It disables the identifier tile when the limit of 10 is reached', async () => {
  mockUseGetIdentifierAttributesCount.mockReturnValue({count: 10});
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_identifier: {}, pim_catalog_text: {}}),
  });

  renderWithProviders(<SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={jest.fn()} />);

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_identifier');
  // DSM Tile renders with aria-disabled="true" when disabled prop is true
  const identifierLabel = screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_identifier');
  const tile = identifierLabel.closest('[aria-disabled="true"]');
  expect(tile).not.toBeNull();
});
