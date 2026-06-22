import React, {useRef} from 'react';
import {useFilterPopupPosition} from './useFilterPopupPosition';
import OperatorDropdown from './OperatorDropdown';

type Props = {
  showLabel: boolean;
  label: string;
  criteriaHint: string;
  canDisable: boolean;
  updateLabel: string;
  isOpen: boolean;
  emptyChoice: boolean;
  operatorChoices: Record<string, string>;
  selectedOperator: string;
  operatorLabel: string;
};

/**
 * The value input of a choice filter, isolated behind `React.memo` with an always-equal comparator
 * so it renders ONCE and React never reconciles its subtree again.
 *
 * This is mandatory for the `'in'` operator: the Backbone shell enhances `input[name="value"]` with
 * Select2, which inserts a `.select2-container` sibling INSIDE this `<div>`. A normal React re-render
 * (e.g. on hint/operator change) would reconcile the `<div>`'s children against the vtree — which has
 * only the `<input>` — and delete the jQuery-added container. Never re-rendering this subtree mirrors
 * how the legacy underscore template rendered the popup body exactly once. The input stays uncontrolled
 * (no `value`/`onChange`) so jQuery `_get/_setInputValue` keep owning the value path.
 */
const ValueField = React.memo(
  () => (
    <div>
      <input type="text" name="value" className="AknTextField select-field" />
    </div>
  ),
  () => true
);
ValueField.displayName = 'ValueField';

/**
 * Presentational chip + criteria popup of a choice/text datagrid filter (C1 Wave 4, Slice C3 — the
 * operator-bearing exemplar, the React `ChoiceBase` that C4's number/identifier/uuid filters reuse).
 *
 * Reproduces, byte-for-byte, the legacy `text-filter.js` chip + `templates/filter/text-filter.html`
 * popup body (with the `emptyChoice` operator block). React owns the popup POSITION (the C2
 * `useFilterPopupPosition` hook) and the operator ACTIVE state (`OperatorDropdown`); jQuery keeps
 * owning popup visibility (`.show()`/`.hide()`), the value path + Select2 (the memoized `ValueField`),
 * and the empty/not-empty value toggling. Renders no `style` prop, so React reconciles away neither
 * jQuery's `display` nor the hook's imperative position on re-render.
 */
const ChoiceFilterCriteria = ({
  showLabel,
  label,
  criteriaHint,
  canDisable,
  updateLabel,
  isOpen,
  emptyChoice,
  operatorChoices,
  selectedOperator,
  operatorLabel,
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
        <div className="AknFilterChoice choicefilter">
          <div className="AknFilterChoice-header">
            <div className="AknFilterChoice-title">{label}</div>
            {emptyChoice && (
              <OperatorDropdown
                operatorChoices={operatorChoices}
                selectedOperator={selectedOperator}
                operatorLabel={operatorLabel}
              />
            )}
          </div>
          <ValueField />
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

export default ChoiceFilterCriteria;
