import $ from 'jquery';
import _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import __ from 'oro/translator';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import TextFilter from 'oro/datafilter/text-filter';
import Select2RestChoiceFilter from 'oro/datafilter/select2-rest-choice-filter';
import initSelect2 from 'pim/initselect2';
import ChoiceFilterCriteria from './ChoiceFilterCriteria';

/**
 * React inner-render of the select2-rest-choice datagrid filter (C1 Wave 5).
 *
 * Sibling of `select2-choice-filter-react` — both legacy filters extend `TextFilter` and share the exact
 * Select2 value-path / operator shape, differing only in the value/display layer this bridge INHERITS
 * (the REST `ChoicesFormatter` ajax + `_getResults` label hydration). So the bridge overrides are
 * identical to select2-choice's: it swaps the underscore template for the shared React
 * `ChoiceFilterCriteria` (whose memoized `React.memo(()=>true)` ValueField keeps the Select2-decorated
 * `input[name="value"]` stable), makes `this._selectedOperator` the source of truth, inits Select2 in
 * `render()` (no `_toggleListSelection` shell) and destroys it in `remove()` (the legacy never did —
 * Select2 portals its dropdown to `<body>`, so unmounting without destroy orphans it: the D5 leak).
 *
 * Added ALONGSIDE `select2-rest-choice-filter.js`; only the `select2-rest-choice` FilterTypeRegistry
 * alias is re-pointed.
 */
export default Select2RestChoiceFilter.extend({
  /**
   * {@inheritdoc}
   *
   * Render the chip+popup with React (position owned by the hook), default the operator, then init
   * Select2 on the memoized input — WITHOUT the legacy underscore template append.
   */
  render: function () {
    AbstractFilter.prototype.render.apply(this, arguments);

    if (_.isUndefined(this._selectedOperator)) {
      this._selectedOperator = this.emptyValue.type;
    }
    this._renderReact();

    this.$(this.criteriaValueSelectors.value).addClass('AknTextField--select2');
    initSelect2.init(this.$(this.criteriaValueSelectors.value), this._getSelect2Config());

    return this;
  },

  /**
   * Render (or reconcile) the React chip + criteria popup into `this.el`.
   *
   * @protected
   */
  _renderReact: function () {
    ReactDOM.render(
      React.createElement(ChoiceFilterCriteria, {
        showLabel: this.showLabel,
        label: this.label,
        criteriaHint: this._getCriteriaHint(),
        canDisable: this.canDisable,
        updateLabel: __('pim_common.update'),
        isOpen: this._criteriaOpen === true,
        emptyChoice: this.emptyChoice,
        operatorChoices: this.operatorChoices,
        selectedOperator: this._selectedOperator,
        operatorLabel: __('pim_common.operator'),
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   *
   * The popup is rendered by React (Select2 is init'd in render); the legacy template append + Select2
   * init is a no-op.
   */
  _renderCriteria: function () {
    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Re-render with the fresh hint rather than a jQuery `.html()`. The memoized ValueField means this
   * never reconciles the Select2 subtree.
   */
  _updateCriteriaHint: function () {
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Keep the inherited (TextFilter) jQuery show, then flip `_criteriaOpen` + re-render to position.
   */
  _showCriteria: function () {
    this._criteriaOpen = true;
    TextFilter.prototype._showCriteria.apply(this, arguments);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Keep the inherited jQuery hide, then flip `_criteriaOpen` + re-render.
   */
  _hideCriteria: function () {
    this._criteriaOpen = false;
    TextFilter.prototype._hideCriteria.apply(this, arguments);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Popup positioning is owned by the `useFilterPopupPosition` hook.
   */
  _updateCriteriaSelectorPosition: function () {
    return this;
  },

  /**
   * {@inheritdoc}
   *
   * The active operator is React state: record it and re-render instead of mutating classes with jQuery.
   */
  _highlightDropdown: function (value) {
    this._selectedOperator = value;
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * On an operator click, record the active operator + re-render (React shows it active), then keep the
   * legacy Select2 enable/disable of the value input.
   */
  _onSelectOperator: function (e) {
    const value = $(e.currentTarget).find('.operator_choice').attr('data-value');
    this._selectedOperator = value;
    this._renderReact();

    if (_.contains(['empty', 'not empty'], value)) {
      this._disableInput();
    } else {
      this._enableInput();
    }

    e.preventDefault();
  },

  /**
   * {@inheritdoc}
   *
   * `_selectedOperator` is the source of truth, so read it directly instead of `.active .operator_choice`.
   * Operators are strings (in/empty/not empty), so no coercion is needed.
   */
  _readDOMValue: function () {
    const operator = this.emptyChoice ? this._selectedOperator : 'in';

    return {
      value: _.contains(['empty', 'not empty'], operator) ? {} : this._getInputValue(this.criteriaValueSelectors.value),
      type: operator,
    };
  },

  /**
   * {@inheritdoc}
   *
   * Read the operator from `_selectedOperator` (source of truth) rather than the `.active .operator_choice`
   * DOM the legacy read — the DOM is one `_renderReact` behind. Otherwise identical to the legacy hint.
   */
  _getCriteriaHint: function () {
    const operator = this.emptyChoice ? this._selectedOperator : undefined;
    if (_.contains(['empty', 'not empty'], operator)) {
      return this.operatorChoices[operator];
    }

    const type = this.getValue().type;
    if (_.contains(['empty', 'not empty'], type)) {
      return this.operatorChoices[type];
    }

    const value = arguments.length > 0 ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();

    return !_.isEmpty(value.value) ? '"' + value.value + '"' : this.placeholder;
  },

  /**
   * {@inheritdoc}
   *
   * Record the operator (so the re-render shows it active) before deferring to TextFilter
   * (→ `_updateCriteriaHint` → `_renderReact`).
   */
  _onValueUpdated: function (newValue, oldValue) {
    this._selectedOperator = newValue.type;
    TextFilter.prototype._onValueUpdated.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   *
   * Destroy Select2 before Backbone tears the element down — it portals its dropdown to `<body>`, so
   * unmounting without destroy orphans it (the D5 leak). The legacy never did this.
   */
  remove: function () {
    if (this.$(this.criteriaValueSelectors.value).data('select2')) {
      this.$(this.criteriaValueSelectors.value).select2('destroy');
    }
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.call(this);
  },
});
