"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.emptyOperation = exports.Operator = void 0;
var Operator;
(function (Operator) {
    Operator["MUL"] = "mul";
    Operator["DIV"] = "div";
    Operator["ADD"] = "add";
    Operator["SUB"] = "sub";
})(Operator || (Operator = {}));
exports.Operator = Operator;
var emptyOperation = function () { return ({
    operator: Operator.MUL,
    value: '',
}); };
exports.emptyOperation = emptyOperation;
//# sourceMappingURL=operation.js.map