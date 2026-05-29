function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
require('backbone');
var Routing = __pimInterop(require('routing'));
var BaseForm = __pimInterop(require('pim/form'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var __ = __pimInterop(require('oro/translator'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var initSelect2 = __pimInterop(require('pim/initselect2'));
var template = __pimInterop(require('pim/template/form/creation/type'));

module.exports = BaseForm.extend({
  options: {},
  template: _.template(template),
  events: {
    'change input': 'updateModel',
  },

  /**
   * Model update callback
   */
  updateModel() {
    const model = this.getFormModel();
    const type = this.$('input').select2('val');
    model.set('type', type);
  },

  /**
   * Fetch group types
   * @param  {HTMLElement}   element  select2 element
   * @param  {Function} callback
   */
  fetchGroupTypes(element, callback) {
    const fetcher = FetcherRegistry.getFetcher('group-type');
    const modelType = this.getFormData().type;

    fetcher.fetchAll().then(types => {
      const results = this.parseResults(types).results;
      const selectedType = modelType || results[0].id;

      this.getFormModel().set('type', selectedType);
      callback(results[0]);
    });
  },

  /**
   * Parses each group type for the select display
   *
   * @param  {Array} types The search results
   * @return {Object}
   */
  parseResults(types) {
    const locale = UserContext.get('catalogLocale');

    const data = {results: []};

    _.reject(types, {
      is_variant: !this.options.config.include_variant,
    }).forEach(value => {
      const {code, labels} = value;
      data.results.push({
        id: code,
        text: i18n.getLabel(labels, locale, code),
      });
    });

    return data;
  },

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render() {
    if (!this.configured) return this;

    const formData = this.getFormData();
    const locale = UserContext.get('catalogLocale');

    this.$el.html(
      this.template({
        label: __('pim_common.type'),
        type: formData.type,
        required: __('pim_common.required_label'),
        isEditable: this.options.config.editable,
      })
    );

    const options = {
      initSelection: this.fetchGroupTypes.bind(this),
      allowClear: false,
      ajax: {
        url: Routing.generate('pim_enrich_grouptype_rest_index'),
        results: this.parseResults.bind(this),
        quietMillis: 250,
        cache: true,
        data(search) {
          return {
            search,
            options: {
              limit: 20,
              locale,
            },
          };
        },
      },
    };

    initSelect2.init(this.$('input'), options).select2('val', []);

    this.delegateEvents();
  },
});
