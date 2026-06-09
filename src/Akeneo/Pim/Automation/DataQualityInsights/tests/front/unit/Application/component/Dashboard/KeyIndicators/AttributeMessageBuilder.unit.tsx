import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AttributeMessageBuilder} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/AttributeMessageBuilder';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => {
    if (key === 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.attributes_to_work_on') {
      return '<improvable_attributes_count_link/>';
    }
    return key;
  },
}));

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AttributeMessageBuilder', () => {
  it('renders nothing when totalToImprove is 0', () => {
    const {container} = renderWith(
      <AttributeMessageBuilder counts={{totalGood: 5, totalToImprove: 0}} onClick={jest.fn()} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders a button when totalToImprove is greater than 0', () => {
    renderWith(<AttributeMessageBuilder counts={{totalGood: 5, totalToImprove: 10}} onClick={jest.fn()} />);
    expect(screen.getByRole('button')).toBeInTheDocument();
  });

  it('calls onClick when the button is clicked', () => {
    const onClick = jest.fn();
    renderWith(<AttributeMessageBuilder counts={{totalGood: 5, totalToImprove: 10}} onClick={onClick} />);
    screen.getByRole('button').click();
    expect(onClick).toHaveBeenCalledTimes(1);
  });
});
