"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useUpdateTemplateProperties = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var react_query_1 = require("react-query");
var apiFetch_1 = require("../tools/apiFetch");
var useUpdateTemplateProperties = function (templateUuid) {
    var url = (0, shared_1.useRoute)('pim_category_template_rest_update', { templateUuid: templateUuid });
    return (0, react_query_1.useMutation)(function (data) { return (0, apiFetch_1.apiFetch)(url, { method: 'PATCH', body: JSON.stringify(data) }); });
};
exports.useUpdateTemplateProperties = useUpdateTemplateProperties;
//# sourceMappingURL=useUpdateTemplateProperties.js.map