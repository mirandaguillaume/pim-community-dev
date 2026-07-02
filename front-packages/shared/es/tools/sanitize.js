var sanitize = function (value) {
    var regex = /[a-zA-Z0-9_]/;
    return value
        .split('')
        .filter(function (char) { return char !== ' '; })
        .map(function (char) { return (char.match(regex) ? char : '_'); })
        .join('')
        .toLocaleLowerCase();
};
export { sanitize };
//# sourceMappingURL=sanitize.js.map