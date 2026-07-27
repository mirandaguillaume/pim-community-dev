import _ from 'underscore';
import __ from 'oro/translator';
import SelectFilterReact from 'oro/datafilter/select-filter-react';

/**
 * React inner-render of the `multiselect` datagrid filter (Vague B, wave 2). Extends the PR1
 * `select-filter-react` bridge — inheriting the React mount/unmount, the `this._selectedValues`
 * (string[]) source of truth, and the whole value plumbing — and adds only the multiselect
 * specifics:
 *  1. `widgetOptions.multiple = true`, so the inherited `_renderReact` renders the DSM
 *     `MultiSelectInput` (via `SelectFilterCriteria`'s `multiple` prop) instead of `SelectInput`.
 *  2. the "All" (`''`) mutual-exclusion previously in legacy `multiselect-filter._onSelectChange`.
 *  3. optgroup flattening — `multiselect` choices may be `{label, value: [{value,label}, …]}` groups,
 *     which the legacy `<select multiple>` rendered as `<optgroup>` but DSM `MultiSelectInput` cannot.
 *
 * Added ALONGSIDE `multiselect-filter.js`; only the `multiselect` FilterTypeRegistry alias is
 * re-pointed. The legacy `classes` string drove the (now removed) jQuery widget and is intentionally
 * dropped — the inherited `render()` never calls `_initializeSelectWidget`, so only `multiple` is read.
 */
export default SelectFilterReact.extend({
  /**
   * Multi mode: the inherited `_renderReact` reads `this.widgetOptions.multiple` to pick
   * `MultiSelectInput` over `SelectInput`.
   *
   * @property
   */
  widgetOptions: {
    multiple: true,
  },

  /**
   * {@inheritdoc}
   *
   * The `multiselect` filter's choices may contain OPTGROUPS — `{label, value: [{value, label}, …]}` —
   * which the legacy `<select multiple>` template rendered as `<optgroup>`. DSM `MultiSelectInput` has no
   * group concept and throws `Duplicate option value [object Object]` if an object is passed as an option
   * value, so flatten optgroups to their leaf options (the real selectable string values — the value read
   * is unchanged). Dedupe by value (DSM rejects repeats; the legacy HTML `<select>` tolerated them). Then
   * translate, sort by label, and prepend the "All" option, exactly as the base `_reactChoices` does. The
   * single-select base (`select`) has no optgroups, so it keeps the inherited flat version.
   *
   * @protected
   */
  _reactChoices: function () {
    const seen: {[value: string]: boolean} = {};
    const flat: {value: string; label: string}[] = [];

    this.choices.forEach((choice: {value: unknown; label: string}) => {
      const leaves = _.isObject(choice.value)
        ? (choice.value as {value: string; label: string}[])
        : [{value: choice.value as string, label: choice.label}];

      _.each(leaves, (leaf: {value: string; label: string}) => {
        if (!seen[leaf.value]) {
          seen[leaf.value] = true;
          flat.push({value: leaf.value, label: __(leaf.label)});
        }
      });
    });

    flat.sort((a, b) => a.label.toString().localeCompare(b.label.toString()));

    if (this.populateDefault) {
      flat.unshift({value: '', label: this.placeholder});
    }

    return flat;
  },

  /**
   * {@inheritdoc}
   *
   * The multi value is the WHOLE selection array (the single base returns only the first element).
   */
  _readDOMValue: function () {
    return {value: this._selectedValues};
  },

  /**
   * Enforce the "All" (`''`) mutual-exclusion, mirroring legacy `multiselect-filter._onSelectChange`:
   * selecting "All" clears every concrete value; selecting a concrete value clears "All"; an empty
   * selection falls back to "All" (`['']`).
   *
   * @param {string[]} values the raw new selection from `MultiSelectInput`
   * @return {string[]} the normalised selection
   * @protected
   */
  _applyAllExclusion: function (values: string[]): string[] {
    // At initialization the model value is `''` (meaning "All"); treat it as `['']` for the diff.
    const previousValue = '' === this.getValue().value ? [''] : this.getValue().value;

    // Did the user just add "All" to remove every previous selection?
    const addAll = _.contains(_.difference(values, previousValue), '');

    values = _.contains(values, '') ? _.without(values, '') : values;
    values = _.isEmpty(values) ? [''] : values;
    values = addAll ? [''] : values;

    return values;
  },

  /**
   * {@inheritdoc}
   *
   * Apply the "All" exclusion before storing, then re-render and push the formatted value (same
   * store→render→setValue shape as the single base).
   */
  _onReactChange: function (values: string[]) {
    this._selectedValues = this._applyAllExclusion(values);
    this._renderReact();
    this.setValue(this._formatRawValue(this._readDOMValue()));
  },
});
