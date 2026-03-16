import React from 'react';
import {IdentifierGeneratorApp} from '../';
import {act, fireEvent, renderWithoutRouter, screen} from '../tests/test-utils';

describe('IdentifierGeneratorApp', () => {
  it('is just an example of unit test', () => {
    window.location.hash = '#/configuration/identifier-generator/';

    renderWithoutRouter(<IdentifierGeneratorApp />);

    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
    act(() => {
      fireEvent.click(screen.getByText('pim_common.create'));
    });
  });
});
