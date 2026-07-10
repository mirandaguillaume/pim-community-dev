import React, {useRef} from 'react';
import {useFilterPopupPosition} from './useFilterPopupPosition';
import OperatorDropdown from './OperatorDropdown';
import FilterOptionDropdown from './FilterOptionDropdown';

type Choice = {value: string; label: string};

type Props = {
  showLabel: boolean;
  label: string;
  criteriaHint: string;
  canDisable: boolean;
  updateLabel: string;
  isOpen: boolean;
  operatorChoices: Record<string, string>;
  selectedOperator: string;
  operatorLabel: string;
  variantClass: 'currencyfilter' | 'unitfilter';
  optionDropdownClass: 'currency' | 'unit';
  optionChoiceClass: 'currency_choice' | 'unit_choice';
  optionHiddenInputName: 'currency_currency' | 'metric_unit';
  optionChoices: Choice[];
  selectedOption: string;
  optionLabel: string;
};

/**
 * The value input of a price/metric filter, isolated behind `React.memo` (always-equal comparator) so
 * it renders ONCE. The number input is uncontrolled (no value/onChange) — jQuery `_get/_setInputValue`
 * own the value path, and never re-reconciling mirrors the legacy underscore template. Bare `<input>`
 * (no wrapping `<div>`), matching `price-filter.html`/`metric-filter.html` (unlike ChoiceFilterCriteria,
 * which wraps it to shield the jQuery Select2 sibling — price/metric have no Select2).
 */
const ValueField = React.memo(
  () => <input type="text" name="value" className="AknTextField select-field" />,
  () => true
);
ValueField.displayName = 'ValueField';

/**
 * Presentational chip + criteria popup shared by the price and metric datagrid filters (C1 Wave 5).
 *
 * Reproduces the legacy `price-filter.html` / `metric-filter.html` popup body: an always-shown operator
 * `AknDropdown` (in `.AknFilterChoice-header`), then `.AknFilterChoice-inputContainer` holding the value
 * input + the currency/unit `FilterOptionDropdown` + its hidden input, then the update button. React owns
 * popup POSITION (`useFilterPopupPosition`) and the operator/option ACTIVE state; jQuery keeps owning
 * popup visibility, the value path, and the input-container show/hide for empty operators.
 */
const NumberUnitFilterCriteria = ({
  showLabel,
  label,
  criteriaHint,
  canDisable,
  updateLabel,
  isOpen,
  operatorChoices,
  selectedOperator,
  operatorLabel,
  variantClass,
  optionDropdownClass,
  optionChoiceClass,
  optionHiddenInputName,
  optionChoices,
  selectedOption,
  optionLabel,
}: Props) => {
  const popupRef = useRef<HTMLDivElement>(null);
  useFilterPopupPosition(popupRef, isOpen);

  return (
    <>
      <div className="AknFilterBox-filter filter-criteria-selector oro-drop-opener">
        {showLabel && <span className="AknFilterBox-filterLabel">{label}</span>}
        <span className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited filter-criteria-hint">
          {criteriaHint}
        </span>
        <span className="AknFilterBox-filterCaret" />
      </div>
      <div ref={popupRef} className="filter-criteria dropdown-menu">
        <div className={`AknFilterChoice ${variantClass} choicefilter`}>
          <div className="AknFilterChoice-header">
            <div className="AknFilterChoice-title">{label}</div>
            <OperatorDropdown
              operatorChoices={operatorChoices}
              selectedOperator={selectedOperator}
              operatorLabel={operatorLabel}
            />
          </div>
          <div className="AknFilterChoice-inputContainer">
            <ValueField />
            <FilterOptionDropdown
              dropdownClass={optionDropdownClass}
              choiceClass={optionChoiceClass}
              hiddenInputName={optionHiddenInputName}
              choices={optionChoices}
              selected={selectedOption}
              label={optionLabel}
            />
          </div>
          <div className="AknFilterChoice-button">
            <button type="button" className="AknButton AknButton--apply filter-update">
              {updateLabel}
            </button>
          </div>
        </div>
      </div>
      {canDisable && <div className="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter" />}
    </>
  );
};

export default NumberUnitFilterCriteria;
