import * as $ from 'jquery';
import * as _ from 'underscore';
import __ from 'oro/translator';
import BaseSelect from 'pim/form/common/fields/select';
import FetcherRegistry from 'pim/fetcher-registry';
import containerTemplate from 'pim/templates/user/form/fields/select-profile-container';

type InterfaceNormalizedProfile = {
  code: string;
  label: string;
};

class SelectProfile extends BaseSelect {
  // @ts-ignore
  private containerTemplate = _.template(containerTemplate);

  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, []),

      FetcherRegistry.getFetcher('user-profiles')
        .fetchAll()
        .then((profiles: InterfaceNormalizedProfile[]) => {
          this.config.choices = profiles;
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  formatChoices(profiles: InterfaceNormalizedProfile[]): {[key: string]: string} {
    return profiles.reduce((result: {[key: string]: string}, profile: InterfaceNormalizedProfile) => {
      result[profile.code] = __(profile.label);

      return result;
    }, {});
  }

  /**
   * {@inheritdoc}
   */
  getFieldValue(field: HTMLInputElement) {
    return null === field.value ? '' : field.value;
  }
}

export = SelectProfile;
