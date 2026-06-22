import React from 'react';

type Props = {
  showLabel: boolean;
  label: string;
  criteriaHint: string;
  canDisable: boolean;
  updateLabel: string;
};

/**
 * Presentational chip + criteria popup of the plain text datagrid filter (C1 Wave 4, Slice C1 —
 * the first popup-filter exemplar).
 *
 * Reproduces, byte-for-byte, the markup of the legacy `text-filter.js` chip template + the
 * `templates/filter/text-filter.html` popup body (with `emptyChoice=false`, i.e. NO operator
 * dropdown). Every load-bearing selector is preserved so the Behat decorators keep working:
 * `.filter-criteria-selector` (BaseDecorator open), `.filter-criteria-hint` (hint text),
 * `.disable-filter` (remove), `.filter-criteria.dropdown-menu` (StringDecorator popup visibility),
 * `input[name="value"].AknTextField.select-field` (value), `.filter-update` (apply).
 *
 * Deliberately renders NO `style` prop: the Backbone shell keeps owning popup visibility/position
 * via jQuery (`.show()`/`.hide()`/`position:fixed`) — React never manages the `style` attribute, so
 * those jQuery writes survive every re-render. The input is uncontrolled (jQuery `_get/_setInputValue`
 * own the value, so StringDecorator's `.val().trigger('change')` still routes).
 */
const TextFilterCriteria = ({showLabel, label, criteriaHint, canDisable, updateLabel}: Props) => (
  <>
    <div className="AknFilterBox-filter filter-criteria-selector oro-drop-opener">
      {showLabel && <span className="AknFilterBox-filterLabel">{label}</span>}
      <span className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited filter-criteria-hint">
        {criteriaHint}
      </span>
      <span className="AknFilterBox-filterCaret" />
    </div>
    <div className="filter-criteria dropdown-menu">
      <div className="AknFilterChoice choicefilter">
        <div className="AknFilterChoice-header">
          <div className="AknFilterChoice-title">{label}</div>
        </div>
        <div>
          <input type="text" name="value" className="AknTextField select-field" />
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

export default TextFilterCriteria;
