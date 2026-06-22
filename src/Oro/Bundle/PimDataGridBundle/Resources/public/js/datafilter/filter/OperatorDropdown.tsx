import React from 'react';

type Props = {
  operatorChoices: Record<string, string>;
  selectedOperator: string;
  operatorLabel: string;
};

/**
 * The operator AknDropdown of a choice/text datagrid filter (C1 Wave 4, Slice C3).
 *
 * Reproduces, byte-for-byte, the `emptyChoice` block of `templates/filter/text-filter.html`:
 * `.AknDropdown.operator` > a `[data-toggle="dropdown"]` button (`.AknActionButton-highlight` =
 * the active operator's label) + `.AknDropdown-menu` listing every operator as a
 * `.AknDropdown-menuLink` (`--active`/`active` on the selected one) > `span.label.operator_choice`
 * carrying `data-value`.
 *
 * Rendered INLINE (never portaled): the Behat `OperatorDecorator::setValue` clicks the
 * `[data-toggle="dropdown"]`, walks ancestors with `getClosest(…, 'AknDropdown')`, and matches an
 * `.operator_choice` by exact text — a portal would move the menu out of the `AknDropdown` ancestor
 * and break that walk. DSM `SelectInput` emits none of these classes, so this stays bespoke HTML
 * (Approach A of the Slice C grounding).
 *
 * React owns the *active* state (the `--active`/`active` class + the highlight label) via
 * `selectedOperator`, because the legacy `_highlightDropdown`/`_onSelectOperator` set it with jQuery
 * and React would reconcile those className mutations away on the next re-render. The `.open` toggle
 * of the menu itself stays with the Bootstrap `data-toggle` plugin (it coincides with the intended
 * close on selection).
 */
const OperatorDropdown = ({operatorChoices, selectedOperator, operatorLabel}: Props) => (
  <div className="AknDropdown operator">
    <div className="AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
      <span className="AknActionButton-highlight">{operatorChoices[selectedOperator]}</span>
      <span className="AknActionButton-caret" />
    </div>
    <div className="AknDropdown-menu">
      <div className="AknDropdown-menuTitle">{operatorLabel}</div>
      {Object.entries(operatorChoices).map(([operator, label]) => (
        <div
          key={operator}
          className={`AknDropdown-menuLink${
            selectedOperator === operator ? ' AknDropdown-menuLink--active active' : ''
          }`}
        >
          <span className="label operator_choice" data-value={operator}>
            {label}
          </span>
        </div>
      ))}
    </div>
  </div>
);

export default OperatorDropdown;
