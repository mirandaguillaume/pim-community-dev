"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.ConfigContext = void 0;
var react_1 = require("react");
var defaultConfigContextValue = {
    operations_max: 5,
    units_max: 50,
    families_max: 100,
};
var ConfigContext = (0, react_1.createContext)(defaultConfigContextValue);
exports.ConfigContext = ConfigContext;
//# sourceMappingURL=config-context.js.map