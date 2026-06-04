import BaseField from 'pimui/js/job/common/edit/field/text';
import editionProvider from 'pim/edition';

class FilePath extends BaseField {
  render() {
    if (false === editionProvider.isCloudEdition()) {
      super.render();
    }

    return this;
  }
}

export = FilePath;
