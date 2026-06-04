import 'jquery';
import 'underscore';
import 'oro/translator';
import BaseAddSelect from 'pim/common/add-select';

export default BaseAddSelect.extend({
  className: 'AknButtonList-item add-attribute-group',

  /**
   * Returns a set of attribute groups that are not empty, and not already added to the family.
   *
   * @param {Object} loadedGroups
   */
  filterItems(loadedGroups) {
    const allowedGroups = {};

    Object.entries(loadedGroups).forEach(([group, data]) => {
      const familyAttributes = this.getRoot()
        .getFormData()
        .attributes.filter(attribute => {
          return attribute.group === group;
        })
        .map(attribute => attribute.code);
      const groupIsNotEmpty = data.attributes.length > 0;
      const groupIsIncomplete = data.attributes.length !== familyAttributes.length;

      if (groupIsNotEmpty && groupIsIncomplete) {
        allowedGroups[group] = data;
      }
    });

    return allowedGroups;
  },
});
