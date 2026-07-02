var camelCaseToSentenceCase = function (value) {
    var result = value.replace(/([A-Z])/g, ' $1');
    return capitalize(result.trim());
};
var capitalize = function (value) {
    return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
};
export { camelCaseToSentenceCase };
//# sourceMappingURL=util.js.map