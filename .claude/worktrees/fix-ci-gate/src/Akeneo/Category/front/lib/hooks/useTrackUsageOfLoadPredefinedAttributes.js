"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useTrackUsageOfLoadPredefinedAttributes = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var apiFetch_1 = require("../tools/apiFetch");
var react_query_1 = require("react-query");
var useTrackUsageOfLoadPredefinedAttributes = function (templateUuid) {
    var url = (0, shared_1.useRoute)('pim_category_template_track_usage_of_load_predefined_attributes', { templateUuid: templateUuid });
    var mutation = (0, react_query_1.useMutation)(function (action) {
        return (0, apiFetch_1.apiFetch)(url, {
            method: 'POST',
            body: JSON.stringify({ action: action }),
        });
    });
    return mutation.mutate;
};
exports.useTrackUsageOfLoadPredefinedAttributes = useTrackUsageOfLoadPredefinedAttributes;
//# sourceMappingURL=useTrackUsageOfLoadPredefinedAttributes.js.map