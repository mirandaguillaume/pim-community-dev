import React from 'react';
import {render} from '@testing-library/react';
import FilterOptionDropdown from '../../../Resources/public/js/datafilter/filter/FilterOptionDropdown';

const choices = [
  {value: 'USD', label: 'USD'},
  {value: 'EUR', label: 'EUR'},
];

describe('FilterOptionDropdown', () => {
  test('renders the AknDropdown with the variant class, the choices, active state, and the hidden input', () => {
    const {container} = render(
      <FilterOptionDropdown
        dropdownClass="currency"
        choiceClass="currency_choice"
        hiddenInputName="currency_currency"
        choices={choices}
        selected="EUR"
        label="Currency"
      />
    );

    expect(container.querySelector('.AknFilterChoice-currency')).not.toBeNull();
    expect(container.querySelector('.AknDropdown.currency [data-toggle="dropdown"]')).not.toBeNull();
    expect(container.querySelector('.AknActionButton-highlight')!.textContent).toBe('EUR');

    const links = container.querySelectorAll('.AknDropdown-menuLink');
    expect(links).toHaveLength(2);
    const usd = container.querySelector('.currency_choice[data-value="USD"]')!;
    const eur = container.querySelector('.currency_choice[data-value="EUR"]')!;
    expect(usd.textContent).toBe('USD');
    expect(eur.closest('.AknDropdown-menuLink')!.className).toContain('AknDropdown-menuLink--active active');
    expect(usd.closest('.AknDropdown-menuLink')!.className).not.toContain('active');

    const hidden = container.querySelector('input[type="hidden"][name="currency_currency"]') as HTMLInputElement;
    expect(hidden).not.toBeNull();
    expect(hidden.value).toBe('EUR');
    expect(hidden.className).toBe('name_input');
  });

  test('falls back to the raw selected value when it is not among the choices (metric before units load)', () => {
    const {container} = render(
      <FilterOptionDropdown
        dropdownClass="unit"
        choiceClass="unit_choice"
        hiddenInputName="metric_unit"
        choices={[{value: 'GRAM', label: 'Gram'}]}
        selected="KILOGRAM"
        label="Unit"
      />
    );
    expect(container.querySelector('.AknActionButton-highlight')!.textContent).toBe('KILOGRAM');
    expect(container.querySelector('.AknDropdown-menu.unit')).toBeNull(); // menu has no variant class (matches price)
  });
});
