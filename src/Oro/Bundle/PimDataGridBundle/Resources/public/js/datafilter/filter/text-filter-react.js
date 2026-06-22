import React from 'react';
import ReactDOM from 'react-dom';
import __ from 'oro/translator';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import TextFilter from 'oro/datafilter/text-filter';
import TextFilterCriteria from './TextFilterCriteria';

/**
 * React inner-render of the plain text datagrid filter (C1 Wave 4, Slice C1).
 *
 * Extends the legacy `TextFilter` to inherit ALL its behaviour (criteria show/hide, the value
 * contract, the popup positioning, the events, getValue/setValue) and overrides only the four
 * methods that render markup, swapping the underscore templates for the React `TextFilterCriteria`.
 * Added ALONGSIDE `text-filter.js` (the legacy class stays — `select2-choice`/`select2-rest-choice`/
 * `ajax-choice` still `TextFilter.extend` it); only the `text` FilterTypeRegistry alias is re-pointed.
 *
 * Popup visibility/position stays jQuery (`_showCriteria`/`_hideCriteria`/`_updateCriteriaSelectorPosition`
 * inherited from TextFilter) — `TextFilterCriteria` renders no `style` prop, so React never clobbers
 * those jQuery writes on re-render, and the uncontrolled input keeps its value across re-renders.
 */
export default TextFilter.extend({
  /**
   * {@inheritdoc}
   *
   * Keep `AbstractFilter.prototype.render` (binds the `.column-inner` scroll handler that positions
   * the popup) + the `change` reposition hook, but render the chip+popup with React instead of the
   * underscore templates.
   */
  render: function () {
    AbstractFilter.prototype.render.apply(this, arguments);

    this._renderReact();

    this.$el.on('change', () => {
      this._updateCriteriaSelectorPosition();
    });

    return this;
  },

  /**
   * Render (or reconcile) the React chip + criteria popup into `this.el`.
   *
   * @protected
   */
  _renderReact: function () {
    ReactDOM.render(
      React.createElement(TextFilterCriteria, {
        showLabel: this.showLabel,
        label: this.label,
        criteriaHint: this._getCriteriaHint(),
        canDisable: this.canDisable,
        updateLabel: __('pim_common.update'),
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   *
   * The criteria popup is rendered by React in `_renderReact`; the legacy underscore append is a
   * no-op here.
   */
  _renderCriteria: function () {
    return this;
  },

  /**
   * {@inheritdoc}
   *
   * The hint lives in the React chip; re-render with the fresh hint rather than a jQuery `.html()`
   * (which React would clobber on the next reconcile). This fires on value-commit (setValue), when
   * the popup is already hidden, so reconciling the popup body is invisible and the uncontrolled
   * input is untouched.
   */
  _updateCriteriaHint: function () {
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Unmount the React tree before Backbone tears the element down (no detached-root leak).
   */
  remove: function () {
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.call(this);
  },
});
