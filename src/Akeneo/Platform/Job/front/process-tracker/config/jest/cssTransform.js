'use strict';

// Custom Jest transformer for CSS imports (Jest 29 compatible).
// Based on react-scripts/config/jest/cssTransform.js but returns {code: string}
// instead of a plain string, as required by @jest/transform in Jest 29.

module.exports = {
  process() {
    return {code: 'module.exports = {};'};
  },
  getCacheKey() {
    return 'cssTransform';
  },
};
