import View from 'pim/form';
import Router from 'pim/router';
import translate from 'oro/translator';

type Config = {
  linkText: string;
  redirectRoute: string;
};

class NavigationBack extends View {
  config: Config;

  initialize({config}: {config: Config}) {
    this.config = config;
  }

  render() {
    this.$el.html(`
        <div class="AknColumn-block">
            <span class="AknColumn-navigationLink navigation-back" tabindex="0" role="button">
                ${translate(this.config.linkText)}
            </span>
        </div>
    `);

    this.delegateEvents({
      'click .navigation-back': this.redirect.bind(this),
    });

    super.render();
  }

  redirect() {
    Router.redirectToRoute(this.config.redirectRoute);
  }
}

export = NavigationBack;
