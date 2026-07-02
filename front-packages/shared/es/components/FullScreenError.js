import React from 'react';
import { ClientErrorIllustration, ServerErrorIllustration } from 'akeneo-design-system';
var FullScreenError = function (_a) {
    var title = _a.title, message = _a.message, code = _a.code;
    var isClientError = code >= 400 && code < 500;
    return (React.createElement("div", { className: "AknInfoBlock AknInfoBlock--error" },
        isClientError ? (React.createElement(ClientErrorIllustration, { width: "auto", height: "auto" })) : (React.createElement(ServerErrorIllustration, { width: "auto", height: "auto" })),
        React.createElement("span", { className: "AknInfoBlock-errorNumber AknInfoBlock-errorNumber--".concat(isClientError ? '400' : '500') }, code),
        React.createElement("h1", null, title),
        React.createElement("div", { className: "AknMessageBox AknMessageBox--danger AknMessageBox--centered" }, message)));
};
export { FullScreenError };
//# sourceMappingURL=FullScreenError.js.map