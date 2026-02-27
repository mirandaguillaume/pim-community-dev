"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.formatSecondsIntl = void 0;
var translateDuration = function (translate) {
    var args = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        args[_i - 1] = arguments[_i];
    }
    var values = args.filter(function (duration) { return duration.value > 0; });
    if (values.length === 0 && args.length > 0) {
        values = [args[args.length - 1]];
    }
    return values
        .map(function (duration) {
        return translate(duration.translationKey, { count: duration.value.toString() }, duration.value);
    })
        .join(' ');
};
var formatSecondsIntl = function (translate, duration) {
    var days = Math.floor(duration / 86400);
    duration = duration - days * 86400;
    var hours = Math.floor(duration / 3600);
    duration = duration - hours * 3600;
    var minutes = Math.floor(duration / 60);
    var seconds = duration - minutes * 60;
    if (days > 0) {
        return translateDuration(translate, { translationKey: 'duration.days', value: days }, { translationKey: 'duration.hours', value: hours });
    }
    if (hours > 0) {
        return translateDuration(translate, { translationKey: 'duration.hours', value: hours }, { translationKey: 'duration.minutes', value: minutes });
    }
    if (minutes > 0) {
        return translateDuration(translate, { translationKey: 'duration.minutes', value: minutes }, { translationKey: 'duration.seconds', value: seconds });
    }
    return translateDuration(translate, { translationKey: 'duration.seconds', value: seconds });
};
exports.formatSecondsIntl = formatSecondsIntl;
//# sourceMappingURL=intl-duration.js.map