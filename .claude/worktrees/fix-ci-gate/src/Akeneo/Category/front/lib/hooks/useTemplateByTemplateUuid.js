"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useTemplateByTemplateUuid = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var react_query_1 = require("react-query");
var react_router_1 = require("react-router");
var apiFetch_1 = require("../tools/apiFetch");
var useTemplateByTemplateUuid = function (uuid) {
    var router = (0, shared_1.useRouter)();
    var translate = (0, shared_1.useTranslate)();
    var notify = (0, shared_1.useNotify)();
    var history = (0, react_router_1.useHistory)();
    var url = router.generate('pim_category_template_rest_get_by_template_uuid', {
        templateUuid: uuid,
    });
    return (0, react_query_1.useQuery)(['get-template', uuid], function () { return (0, apiFetch_1.apiFetch)(url, {}); }, {
        enabled: null !== uuid,
        onError: function () {
            history.push('/');
            notify(shared_1.NotificationLevel.ERROR, translate('akeneo.category.template.not_found'));
        },
    });
};
exports.useTemplateByTemplateUuid = useTemplateByTemplateUuid;
//# sourceMappingURL=useTemplateByTemplateUuid.js.map