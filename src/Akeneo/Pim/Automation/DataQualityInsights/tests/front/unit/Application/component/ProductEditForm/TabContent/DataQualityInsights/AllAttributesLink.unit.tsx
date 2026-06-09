import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import AllAttributesLink from '../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AllAttributesLink';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
} from '../../../../../../../../front/src/application/listener/ProductEditForm/ProductContextListener';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AllAttributesLink', () => {
  let dispatchSpy: jest.SpyInstance;

  beforeEach(() => {
    dispatchSpy = jest.spyOn(window, 'dispatchEvent');
  });

  afterEach(() => {
    dispatchSpy.mockRestore();
  });

  it('renders the translated label', () => {
    renderWith(<AllAttributesLink axis="enrichment" attributes={[]} />);
    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.enrichment.attributes_link')
    ).toBeInTheDocument();
  });

  it('dispatches filter_all_missing_attributes when axis is enrichment', () => {
    renderWith(<AllAttributesLink axis="enrichment" attributes={['name', 'description']} />);
    userEvent.click(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.enrichment.attributes_link')
    );
    expect(dispatchSpy).toHaveBeenCalledTimes(1);
    const event = dispatchSpy.mock.calls[0][0] as CustomEvent;
    expect(event.type).toBe(DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES);
    expect(event.detail.attributes).toEqual(['name', 'description']);
  });

  it('dispatches filter_all_improvable_attributes when axis is consistency', () => {
    renderWith(<AllAttributesLink axis="consistency" attributes={['brand']} />);
    userEvent.click(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.consistency.attributes_link')
    );
    expect(dispatchSpy).toHaveBeenCalledTimes(1);
    const event = dispatchSpy.mock.calls[0][0] as CustomEvent;
    expect(event.type).toBe(DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES);
    expect(event.detail.attributes).toEqual(['brand']);
  });
});
