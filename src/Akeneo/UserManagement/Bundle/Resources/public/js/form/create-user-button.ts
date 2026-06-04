import CreateButton from 'pim/form/common/index/create-button';
import FeatureFlags from 'pim/feature-flags';

class CreateUserButton extends CreateButton {
  public render() {
    if (FeatureFlags.isEnabled('free_trial')) {
      this.$el.remove();

      return this;
    }

    return super.render();
  }
}

export = CreateUserButton;
