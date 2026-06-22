import ReactDOM from 'react-dom';
import AbstractFilter from 'oro/datafilter/abstract-filter';

/**
 * Base for datagrid filters whose inner UI is rendered with React (C1 Wave 4).
 *
 * `AbstractFilter` is a raw `Backbone.View` (no `renderReact`), so this base owns the
 * `ReactDOM.render`/`unmountComponentAtNode` lifecycle in one place — the filter analogue of
 * `ReactCellBase` (datagrid/cell/react-cell-base). Subclasses stay thin: they override
 * `reactElement()` to return their React element (built from the filter's value/label), and the rest
 * of the AbstractFilter contract (getValue/setValue/_get/_setInputValue, enable/disable/show/hide/
 * reset, the Backbone `events` delegation) keeps working unchanged on the React-rendered DOM, because
 * the inputs are uncontrolled and jQuery remains the value owner.
 *
 * `render()` deliberately does NOT call `AbstractFilter.prototype.render`: that binds a document-wide
 * `.column-inner` scroll handler used only to reposition criteria popups, which React filters do not
 * use (popup positioning will be a dedicated React hook in a later slice). A subclass that needs a
 * post-render step (e.g. the search filter's `enableReadonly()`) overrides `render()` and calls
 * `ReactFilterBase.prototype.render` first.
 */
export default AbstractFilter.extend({
  /**
   * Subclasses override this to return the React element to render into `this.el`
   * (or `null` to render nothing).
   *
   * @return {?React.ReactElement}
   */
  reactElement: function () {
    return null;
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    ReactDOM.render(this.reactElement(), this.el);

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Unmount the React tree before Backbone tears the element down (no detached-root leak).
   */
  remove: function () {
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.apply(this, arguments);
  },
});
