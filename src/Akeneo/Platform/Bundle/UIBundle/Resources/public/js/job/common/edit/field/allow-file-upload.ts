import BaseField from 'pimui/js/job/common/edit/field/switch';
import editionProvider from 'pim/edition';

class AllowFileUpload extends BaseField {
  render() {
    if (editionProvider.isCloudEdition() === false) {
      super.render();
    }

    return this;
  }
}

export = AllowFileUpload;
