import BaseView from 'pim/common/simple-view';
import FeatureFlags from 'pim/feature-flags';

class UserProfileTabContent extends BaseView {
  public configure() {
    if (FeatureFlags.isEnabled('free_trial')) {
      Object.values(this.extensions).forEach((extension: any) => {
        extension.readOnly = true;
      });
    }

    BaseView.prototype.configure.apply(this, []);
  }
}

export = UserProfileTabContent;
