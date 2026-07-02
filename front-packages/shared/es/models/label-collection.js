var isLabelCollection = function (labelCollection) {
    if (undefined === labelCollection || typeof labelCollection !== 'object') {
        return false;
    }
    return !Object.keys(labelCollection).some(function (key) { return typeof key !== 'string' || typeof labelCollection[key] !== 'string'; });
};
var getLabel = function (labels, locale, fallback) {
    return labels && labels[locale] ? labels[locale] : "[".concat(fallback, "]");
};
export { getLabel, isLabelCollection };
//# sourceMappingURL=label-collection.js.map