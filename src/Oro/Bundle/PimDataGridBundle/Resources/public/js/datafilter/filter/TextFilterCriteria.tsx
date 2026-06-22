import React, {useRef} from 'react';
import {useFilterPopupPosition} from './useFilterPopupPosition';

type Props = {
  showLabel: boolean;
  label: string;
  criteriaHint: string;
  canDisable: boolean;
  updateLabel: string;
  isOpen: boolean;
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
 * The input renders NO `value`/`onChange` (uncontrolled): jQuery `_get/_setInputValue` own the value,
 * so StringDecorator's `.val().trigger('change')` still routes and the value survives re-renders.
 *
 * Popup visibility (`display`) stays jQuery (`_showCriteria`/`_hideCriteria` `.show()`/`.hide()`), but
 * the popup POSITION (`position:fixed`/`top`/`left`) is owned by `useFilterPopupPosition` (C2): the
 * Backbone shell flips `isOpen` on show/hide and the hook writes the position imperatively through the
 * ref. The popup still renders no `style` prop, so React reconciles away neither jQuery's `display`
 * nor the hook's position on re-render.
 */
const TextFilterCriteria = ({showLabel, label, criteriaHint, canDisable, updateLabel, isOpen}: Props) => {
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
};

export default TextFilterCriteria;
