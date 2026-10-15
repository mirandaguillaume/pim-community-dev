import React from 'react';

type Choice = {value: string; label: string};

type Props = {
  label: string;
  choices: Choice[];
  selected: string;
  onChange: (value: string) => void;
};

/**
 * Controlled React view of the product grid's scope switcher (`product_scope` datagrid filter).
 *
 * Replaces `templates/filter/scope-filter.html` one node at a time rather than reusing
 * `SelectFilterCriteria`: the scope switcher is NOT a filter-criteria select but a bespoke
 * `AknDropdown` living in the grid toolbar, so rendering it through the DSM `SelectInput` would
 * change what the user sees. `FilterOptionDropdown` is the closest sibling but puts `data-value` on
 * the inner `<span>`, while this markup's contract puts it on the `.AknDropdown-menuLink` itself.
 *
 * Three details are load-bearing and deliberately preserved:
 *
 * - `data-toggle="dropdown"` on the trigger. The Bootstrap plugin owns opening the menu and toggles
 *   its class on the trigger's PARENT — which is the bridge's `this.el`, owned by Backbone and
 *   outside this tree — so a React re-render cannot wipe the open state.
 * - `data-value` on the `.AknDropdown-menuLink` div, not on the inner label. The legacy
 *   `_onSelectChange` read it via `closest('.AknDropdown-menuLink').attr('data-value')`.
 * - `AknDropdown-menuLink--active` alone, without the extra `active` class `FilterOptionDropdown`
 *   also emits: legacy `highlightScope()` set only this one.
 *
 * The `.value` span holds the selected label, which the legacy injected imperatively with
 * `.html()`. An unknown `selected` renders it empty, mirroring the template's initial state before
 * `highlightScope()` had run.
 */
const ScopeFilterCriteria = ({label, choices, selected, onChange}: Props) => {
  const selectedChoice = choices.find(choice => choice.value === selected);

  return (
    <>
      <div className="AknFilterBox-filter filter-select" data-toggle="dropdown">
        <span className="AknFilterBox-filterLabel">{label}</span>
        <span className="AknFilterBox-filterCriteria value">{selectedChoice ? selectedChoice.label : ''}</span>
        <span className="AknFilterBox-filterCaret" />
      </div>
      <div className="AknDropdown-menu">
        <div className="AknDropdown-menuTitle">{label}</div>
        {choices.map(choice => (
          <div
            key={choice.value}
            className={`AknDropdown-menuLink${selected === choice.value ? ' AknDropdown-menuLink--active' : ''}`}
            data-value={choice.value}
            onClick={() => onChange(choice.value)}
          >
            <span className="label">{choice.label}</span>
          </div>
        ))}
      </div>
    </>
  );
};

export default ScopeFilterCriteria;
