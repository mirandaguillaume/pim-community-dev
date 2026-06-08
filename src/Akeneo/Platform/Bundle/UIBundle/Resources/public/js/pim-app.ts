import _ from 'underscore';
import Backbone from 'backbone';
import BaseView from 'pimui/js/view/base';
import {systemConfiguration} from '@akeneo-pim-community/shared';

import mediator from 'oro/mediator';
import FetcherRegistry from 'pim/fetcher-registry';
import init from 'pim/init';
import initTranslator from 'pim/init-translator';
import initLayout from 'oro/init-layout';
import initSignin from 'pimuser/js/init-signin';
import pageTitle from 'pim/page-title';
import DateContext from 'pim/date-context';
import UserContext from 'pim/user-context';
import template from 'pim/template/app';

class PimApp extends BaseView {
  private readonly template = _.template(template);

  public events() {
    return {
      'click #overlay': 'onClickToCollapsePanel',
    };
  }

  constructor() {
    super({tagName: 'div', className: 'app'});
  }

  public initialize(): void {
    initLayout();
    initSignin();
  }

  public configure() {
    this.listenTo(mediator, 'pim-app:overlay:show', this.showOverlay);
    this.listenTo(mediator, 'pim-app:overlay:hide', this.hideOverlay);

    return $.when(
      FetcherRegistry.initialize(),
      DateContext.initialize(),
      UserContext.initialize(),
      systemConfiguration.initialize()
    )
      .then(initTranslator.fetch)
      .then(() => {
        init();

        pageTitle.set('Akeneo PIM');

        return super.configure();
      });
  }

  public render(): BaseView {
    this.$el.html(this.template({}));

    if (!Backbone.History.started) {
      Backbone.history.start();
    }

    return BaseView.prototype.render.apply(this, []);
  }

  public onClickToCollapsePanel(): void {
    mediator.trigger('pim-app:panel:close');
  }

  private showOverlay(): void {
    this.$('#overlay').addClass('AknOverlay--show');
  }

  private hideOverlay(): void {
    this.$('#overlay').removeClass('AknOverlay--show');
  }
}

export = PimApp;
