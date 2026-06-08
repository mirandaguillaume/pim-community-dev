import * as _ from 'underscore';
import {EventsHash} from 'backbone';

import $ from 'jquery';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import UserContext from 'pim/user-context';
import FetcherRegistry from 'pim/fetcher-registry';
import * as i18n from 'pim/i18n';
import template from 'pim/template/product/locale-switcher';

interface Locale {
  code: string;
  language: string;
}

class LocaleSwitcher extends BaseForm {
  private template = _.template(template);

  private locales: Locale[] = [];

  constructor(options: any) {
    super({...options, ...{className: 'AknDropdown AknButtonList-item locale-switcher'}});
  }

  public events(): EventsHash {
    return {
      'click [data-locale]': 'changeLocale',
    };
  }

  configure() {
    return $.when(
      BaseForm.prototype.configure.apply(this, []),
      this.fetchLocales().then((locales: Locale[]) => {
        this.locales = locales;
        const currentLocaleCode = UserContext.get('catalogLocale');
        let currentLocale = this.locales.find((locale: Locale) => locale.code === currentLocaleCode);
        if (undefined === currentLocale) {
          [currentLocale] = this.locales;
          UserContext.set('catalogLocale', currentLocale.code);
        }
      })
    );
  }

  render() {
    const currentLocaleCode = UserContext.get('catalogLocale');
    let currentLocale = this.locales.find((locale: Locale) => locale.code === currentLocaleCode);

    this.$el.html(
      this.template({
        locales: this.locales,
        currentLocale,
        i18n: i18n,
        displayInline: false,
        displayLabel: true,
        label: __('pim_enrich.entity.locale.uppercase_label'),
      })
    );

    return this;
  }

  changeLocale(event: any) {
    UserContext.set('catalogLocale', event.currentTarget.dataset.locale);
    this.getRoot().trigger('pim_enrich:form:locale_switcher:change', {
      localeCode: event.currentTarget.dataset.locale,
      context: 'base_product',
    });
    this.render();
  }

  fetchLocales() {
    return FetcherRegistry.getFetcher('locale').fetchActivated();
  }
}

export = LocaleSwitcher;
