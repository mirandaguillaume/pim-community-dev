"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getChannelTranslation = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
function getChannelTranslation(channelList, channelCode, locale) {
    var _a;
    var retrievedChannel = (_a = channelList.find(function (channel) { return channel.code === channelCode; })) !== null && _a !== void 0 ? _a : null;
    return retrievedChannel && (0, shared_1.getLabel)(retrievedChannel.labels, locale, "" + channelCode);
}
exports.getChannelTranslation = getChannelTranslation;
//# sourceMappingURL=channelHelper.js.map