var view = {
    setElement: function () { return view; },
    render: function () { },
    remove: function () { },
    setData: function () { },
};
var mockedDependencies = {
    router: {
        generate: function (route) { return route; },
        redirect: function (url) { return url; },
        redirectToRoute: function (route) { return route; },
    },
    translate: function (key) { return key; },
    viewBuilder: {
        build: function (_viewName) { return Promise.resolve(view); },
    },
    notify: function (level, message) { return "".concat(level, " ").concat(message); },
    user: {
        get: function (data) {
            switch (data) {
                case 'catalogLocale':
                    return 'en_US';
                case 'uiLocale':
                    return 'en_US';
                case 'timezone':
                    return 'UTC';
                default:
                    return data;
            }
        },
        set: function () { },
    },
    security: {
        isGranted: function (_acl) { return true; },
    },
    mediator: {
        trigger: function (event) { return event; },
        on: function (event, _callback) { return event; },
        off: function (event, _callback) { return event; },
    },
    featureFlags: {
        isEnabled: function (_feature) { return true; },
    },
    analytics: {
        track: function (event) { return event; },
        appcuesTrack: function (event) { return event; },
    },
    systemConfiguration: {
        initialize: function () { return Promise.resolve(); },
        refresh: function () { return Promise.resolve(); },
        get: function (key) { return key; },
    },
};
export { mockedDependencies };
//# sourceMappingURL=mockedDependencies.js.map