import React from 'react';

type Choice = {value: string; label: string};

type Props = {
  dropdownClass: 'currency' | 'unit';
  choiceClass: 'currency_choice' | 'unit_choice';
  hiddenInputName: 'currency_currency' | 'metric_unit';
  choices: Choice[];
  selected: string;
  label: string;
};

/**
 * Generic native AknDropdown for the currency (price) / unit (metric) option of a number filter
 * (C1 Wave 5). A parametrized sibling of `OperatorDropdown` (which stays operator-specific, untouched).
 *
 * React owns the *active* state (the `--active`/`active` class + the highlight label) and the hidden
 * input value; the Bootstrap `data-toggle` plugin owns the menu open/close. Rendered INLINE (never
 * portaled): the Behat decorator clicks the `[data-toggle="dropdown"]` and walks
 * `getClosest(…, 'AknDropdown')` to match a `.<choiceClass>` by text — a portal would break that walk.
 *
 * The hidden `input[name=…]` is a sibling of the `.AknFilterChoice-<variant>` block (both live inside
 * the criteria's `.AknFilterChoice-inputContainer`), mirroring `price-filter.html`/`metric-filter.html`.
 */
const FilterOptionDropdown = ({dropdownClass, choiceClass, hiddenInputName, choices, selected, label}: Props) => {
  const selectedChoice = choices.find(choice => choice.value === selected);
  const selectedLabel = selectedChoice ? selectedChoice.label : selected;

  return (
    <>
      <div className={`AknFilterChoice-${dropdownClass}`}>
        <div className={`AknDropdown ${dropdownClass}`}>
          <div className="AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
            <span className="AknActionButton-highlight">{selectedLabel}</span>
            <span className="AknActionButton-caret" />
          </div>
          <div className="AknDropdown-menu">
            <div className="AknDropdown-menuTitle">{label}</div>
            {choices.map(choice => (
              <div
                key={choice.value}
                className={`AknDropdown-menuLink${
                  selected === choice.value ? ' AknDropdown-menuLink--active active' : ''
                }`}
              >
                <span className={`label ${choiceClass}`} data-value={choice.value}>
                  {choice.label}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
      <input className="name_input" type="hidden" name={hiddenInputName} value={selected} readOnly />
    </>
  );
};

export default FilterOptionDropdown;
