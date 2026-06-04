import * as $ from 'jquery';

import BaseController from 'pim/controller/base';
import Router from 'pim/router';

class EntityCategoriesWithFamilyVariant extends BaseController {
  renderRoute(route: any): any {
    sessionStorage.setItem('redirectTab', '#' + this.options.config.redirectTabName);
    sessionStorage.setItem('current_column_tab', this.options.config.redirectTabName);

    Router.redirectToRoute(this.options.config.redirectRouteName, route.params);

    return $.Deferred().resolve();
  }
}

export = EntityCategoriesWithFamilyVariant;
