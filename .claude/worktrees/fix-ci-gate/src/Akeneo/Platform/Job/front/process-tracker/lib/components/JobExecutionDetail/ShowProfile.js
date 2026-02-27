"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ShowProfile = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var ShowProfile = function (_a) {
    var jobInstance = _a.jobInstance;
    var translate = (0, shared_1.useTranslate)();
    var router = (0, shared_1.useRouter)();
    if (!['import', 'export'].includes(jobInstance.type))
        return null;
    var route = 'pim_importexport_%type%_profile_show'.replace('%type%', jobInstance.type);
    var href = "#" + router.generate(route, { code: jobInstance.code });
    return react_1.default.createElement(akeneo_design_system_1.Link, { href: href }, translate('pim_import_export.form.job_execution.button.show_profile.title'));
};
exports.ShowProfile = ShowProfile;
//# sourceMappingURL=ShowProfile.js.map