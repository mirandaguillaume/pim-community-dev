import React, {useEffect, useRef} from 'react';
import $ from 'jquery';
import Datepicker from 'datepicker';
import {useFilterPopupPosition} from './useFilterPopupPosition';
import OperatorDropdown from './OperatorDropdown';

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
  inputClass: string;
  from: string;
  to: string;
  fromLabel: string;
  toLabel: string;
  datetimepickerOptions: object;
};

type DateValueFieldProps = Pick<
  Props,
  'inputClass' | 'from' | 'to' | 'fromLabel' | 'toLabel' | 'datetimepickerOptions'
>;

/**
 * The date-range value body of the date filter, isolated behind `React.memo` with an always-equal
 * comparator so it renders ONCE and React never reconciles its subtree again.
 *
 * This is mandatory: a jQuery `bootstrap.datetimepicker` is attached to each `.from`/`.to` span (its
 * calendar is portaled to `<body>`). A normal React re-render (on hint/operator/open change) would
 * reconcile the datepicker-mutated DOM away; never re-rendering this subtree keeps it intact, exactly
 * as the legacy underscore template rendered the popup body once. The inputs stay uncontrolled (no
 * `value`/`onChange`) so jQuery `_get/_setInputValue` + the datepicker keep owning the value path — and
 * Selenium's native `setValue` + `change` reach the DOM directly.
 *
 * The effect DESTROYS both widgets on unmount. The datepicker portals its calendar and window/document
 * handlers to `<body>`, so skipping `destroy()` orphans them there — the exact leak shape that hung the
 * product save in the D5 associations-modal regression.
 */
const DateValueField = React.memo(
  ({inputClass, from, to, fromLabel, toLabel, datetimepickerOptions}: DateValueFieldProps) => {
    const fromRef = useRef<HTMLSpanElement>(null);
    const toRef = useRef<HTMLSpanElement>(null);

    useEffect(() => {
      const $from = Datepicker.init($(fromRef.current), datetimepickerOptions);
      const $to = Datepicker.init($(toRef.current), datetimepickerOptions);

      return () => {
        [$from, $to].forEach(($widget: any) => {
          const picker = $widget.data('datetimepicker');
          if (picker && typeof picker.destroy === 'function') {
            picker.destroy();
          }
        });
      };
    }, []);

    return (
      <div className="AknFilterChoice-dates">
        <span ref={fromRef} className="AknFilterChoice-date from">
          <input
            type="text"
            defaultValue={from}
            className={`date-selector ${inputClass} add-on`}
            name="start"
            placeholder={fromLabel}
            size={1}
          />
        </span>
        <span className="AknFilterChoice-separator separator">-</span>
        <span ref={toRef} className="AknFilterChoice-date to">
          <input
            type="text"
            defaultValue={to}
            className={`date-selector ${inputClass} add-on`}
            name="end"
            placeholder={toLabel}
            size={1}
          />
        </span>
      </div>
    );
  },
  () => true
);
DateValueField.displayName = 'DateValueField';

/**
 * Presentational chip + criteria popup of the date datagrid filter (C1 Wave 5 — the first
 * Datepicker-bearing filter migrated to React). Follows the `choice-filter-react` pattern verbatim:
 * React owns the popup POSITION (`useFilterPopupPosition`) and the operator ACTIVE state
 * (`OperatorDropdown`, fed `selectedOperator`); jQuery keeps owning popup visibility, the per-operator
 * `.from`/`.to`/separator show-hide (`_displayFilterType`), and the value path + the two datepickers
 * (the memoized `DateValueField`). Renders no `style` prop, so React reconciles away neither jQuery's
 * `display` nor the hook's imperative position on re-render. Reproduces `templates/filter/date-filter`
 * byte-for-byte so the Behat contract (`.operator_choice`/`.active`, `input[name=start|end]`,
 * `.filter-update`, per-operator span visibility) holds.
 */
const DateFilterCriteria = ({
  showLabel,
  label,
  criteriaHint,
  canDisable,
  updateLabel,
  isOpen,
  operatorChoices,
  selectedOperator,
  operatorLabel,
  inputClass,
  from,
  to,
  fromLabel,
  toLabel,
  datetimepickerOptions,
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
        <div className="AknFilterChoice">
          <div className="AknFilterChoice-header">
            <div className="AknFilterChoice-title">{label}</div>
            <OperatorDropdown
              operatorChoices={operatorChoices}
              selectedOperator={selectedOperator}
              operatorLabel={operatorLabel}
            />
          </div>
          <DateValueField
            inputClass={inputClass}
            from={from}
            to={to}
            fromLabel={fromLabel}
            toLabel={toLabel}
            datetimepickerOptions={datetimepickerOptions}
          />
          <div className="AknFilterChoice-button">
            <button className="AknButtonList-item AknButton AknButton--apply filter-update" type="button">
              {updateLabel}
            </button>
          </div>
        </div>
      </div>
      {canDisable && <div className="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter" />}
    </>
  );
};

export default DateFilterCriteria;
