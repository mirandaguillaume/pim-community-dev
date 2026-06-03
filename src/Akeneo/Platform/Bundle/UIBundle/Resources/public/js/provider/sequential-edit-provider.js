'use strict';
module.exports = {
  /**
   * Set the collection with the given parameter
   *
   * @param {array} entities
   */
  set: function (entities) {
    sessionStorage.setItem('sequential_edit_entities', JSON.stringify(entities));
    sessionStorage.setItem('sequential_edit_current_index', 0);
  },

  /**
   * Clear the locale storage
   */
  clear: function () {
    sessionStorage.setItem('sequential_edit_entities', JSON.stringify([]));
  },

  /**
   * Get the sequential edit collection
   *
   * @return {array}
   */
  get: function () {
    return null === sessionStorage.getItem('sequential_edit_entities')
      ? []
      : JSON.parse(sessionStorage.getItem('sequential_edit_entities'));
  },

  getIndex: function () {
    return parseInt(sessionStorage.getItem('sequential_edit_current_index') || '0');
  },

  setIndex: function (i) {
    sessionStorage.setItem('sequential_edit_current_index', i);
  },
};
