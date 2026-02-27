"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.templateFormReducer = void 0;
var templateFormReducer = function (previousState, action) {
    var _a, _b, _c, _d, _e, _f, _g, _h;
    var state = window.structuredClone(previousState);
    if (action.type === 'attribute_label_translation_changed') {
        var _j = action.payload, attributeUuid = _j.attributeUuid, localeCode = _j.localeCode, value = _j.value;
        state.attributes[attributeUuid] = state.attributes[attributeUuid] || { labels: {} };
        state.attributes[attributeUuid].labels[localeCode] = state.attributes[attributeUuid].labels[localeCode] || {};
        state.attributes[attributeUuid].labels[localeCode] = {
            value: value,
            errors: [],
        };
    }
    if (action.type === 'attribute_label_translation_saved') {
        var _k = action.payload, attributeUuid = _k.attributeUuid, localeCode = _k.localeCode, value = _k.value;
        if (((_d = (_c = (_b = (_a = state.attributes) === null || _a === void 0 ? void 0 : _a[attributeUuid]) === null || _b === void 0 ? void 0 : _b.labels) === null || _c === void 0 ? void 0 : _c[localeCode]) === null || _d === void 0 ? void 0 : _d.value) === value) {
            delete state.attributes[attributeUuid].labels[localeCode];
        }
    }
    if (action.type === 'save_attribute_label_translation_failed') {
        var _l = action.payload, attributeUuid = _l.attributeUuid, localeCode = _l.localeCode, errors = _l.errors;
        if ((_g = (_f = (_e = state.attributes) === null || _e === void 0 ? void 0 : _e[attributeUuid]) === null || _f === void 0 ? void 0 : _f.labels) === null || _g === void 0 ? void 0 : _g[localeCode]) {
            state.attributes[attributeUuid].labels[localeCode].errors = errors;
        }
    }
    if (action.type === 'template_label_translation_changed') {
        var _m = action.payload, localeCode = _m.localeCode, value = _m.value;
        state.properties.labels[localeCode] = {
            value: value,
            errors: [],
        };
    }
    if (action.type === 'template_label_translation_saved') {
        var _o = action.payload, localeCode = _o.localeCode, value = _o.value;
        if (((_h = state.properties.labels[localeCode]) === null || _h === void 0 ? void 0 : _h.value) === value) {
            delete state.properties.labels[localeCode];
        }
    }
    if (action.type === 'save_template_label_translation_failed') {
        var _p = action.payload, localeCode = _p.localeCode, errors = _p.errors;
        if (state.properties.labels[localeCode]) {
            state.properties.labels[localeCode].errors = errors;
        }
    }
    console.debug(action, previousState, state);
    return state;
};
exports.templateFormReducer = templateFormReducer;
//# sourceMappingURL=templateFormReducer.js.map