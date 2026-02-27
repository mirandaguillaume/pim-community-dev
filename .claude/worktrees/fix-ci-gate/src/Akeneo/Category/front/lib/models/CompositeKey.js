"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.buildCompositeKey = void 0;
var COMPOSITE_KEY_SEPARATOR = '|';
function buildCompositeKey(attribute, channelCode, localeCode) {
    if (channelCode === void 0) { channelCode = null; }
    if (localeCode === void 0) { localeCode = null; }
    var code = attribute.code, uuid = attribute.uuid;
    var components = [code, uuid];
    if (channelCode && attribute.is_scopable) {
        components.push(channelCode);
    }
    if (localeCode && attribute.is_localizable) {
        components.push(localeCode);
    }
    return components.join(COMPOSITE_KEY_SEPARATOR);
}
exports.buildCompositeKey = buildCompositeKey;
//# sourceMappingURL=CompositeKey.js.map