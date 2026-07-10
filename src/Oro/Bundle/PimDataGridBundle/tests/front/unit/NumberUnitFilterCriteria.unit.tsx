import React from 'react';
import {render} from '@testing-library/react';
import NumberUnitFilterCriteria from '../../../Resources/public/js/datafilter/filter/NumberUnitFilterCriteria';

jest.mock('../../../Resources/public/js/datafilter/filter/useFilterPopupPosition', () => ({
  useFilterPopupPosition: jest.fn(),
}));

const baseProps = {
  showLabel: true,
  label: 'Price',
  criteriaHint: 'All',
  canDisable: true,
  updateLabel: 'Update',
  isOpen: false,
  operatorChoices: {'1': '=', '2': '>'},
  selectedOperator: '1',
  operatorLabel: 'Operator',
  variantClass: 'currencyfilter' as const,
  optionDropdownClass: 'currency' as const,
  optionChoiceClass: 'currency_choice' as const,
  optionHiddenInputName: 'currency_currency' as const,
  optionChoices: [{value: 'USD', label: 'USD'}],
  selectedOption: 'USD',
  optionLabel: 'Currency',
};

describe('NumberUnitFilterCriteria', () => {
  test('renders the chip, the variant wrapper, operator dropdown, value input, option dropdown and update button', () => {
    const {container} = render(<NumberUnitFilterCriteria {...baseProps} />);

    expect(container.querySelector('.filter-criteria-selector .filter-criteria-hint')!.textContent).toBe('All');
    expect(container.querySelector('.AknFilterChoice.currencyfilter.choicefilter')).not.toBeNull();
    expect(container.querySelector('.AknFilterChoice-header .AknDropdown.operator')).not.toBeNull();

    const inputContainer = container.querySelector('.AknFilterChoice-inputContainer')!;
    expect(inputContainer.querySelector('input[name="value"].AknTextField.select-field')).not.toBeNull();
    expect(inputContainer.querySelector('.AknFilterChoice-currency .AknDropdown.currency')).not.toBeNull();
    expect(inputContainer.querySelector('input[type="hidden"][name="currency_currency"]')).not.toBeNull();

    expect(container.querySelector('.AknFilterChoice-button .filter-update')!.textContent).toBe('Update');
    expect(container.querySelector('.disable-filter')).not.toBeNull();
  });

  test('omits the label span and the disable-filter when showLabel/canDisable are false', () => {
    const {container} = render(<NumberUnitFilterCriteria {...baseProps} showLabel={false} canDisable={false} />);
    expect(container.querySelector('.AknFilterBox-filterLabel')).toBeNull();
    expect(container.querySelector('.disable-filter')).toBeNull();
  });
});
