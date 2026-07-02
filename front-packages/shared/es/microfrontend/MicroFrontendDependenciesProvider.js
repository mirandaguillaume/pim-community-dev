var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useIsMounted } from '../hooks';
import { DependenciesContext } from '../DependenciesContext';
import { useNotifications } from './useNotifications';
import { Notifications } from '../components';
import { createQueryParam } from './model/queryParam';
import { initTranslator, translate, userContext as LegacyUserContext } from '../dependencies';
var fetcher = function (url) { return __awaiter(void 0, void 0, void 0, function () {
    var response;
    return __generator(this, function (_a) {
        switch (_a.label) {
            case 0: return [4, fetch(url)];
            case 1:
                response = _a.sent();
                if (401 === response.status) {
                    throw new Error('You are not logged in the PIM');
                }
                return [4, response.json()];
            case 2: return [2, _a.sent()];
        }
    });
}); };
var MicroFrontendDependenciesProvider = function (_a) {
    var routes = _a.routes, translations = _a.translations, children = _a.children;
    var _b = useState({}), securityContext = _b[0], setSecurityContext = _b[1];
    var _c = useState({ get: function (key) { return key; }, set: function () { } }), userContext = _c[0], setUserContext = _c[1];
    var _d = useNotifications(), notifications = _d[0], notify = _d[1], handleNotificationClose = _d[2];
    var isMounted = useIsMounted();
    var _e = useState(function () {
        if (translations !== undefined) {
            console.warn('The "translations" option MicroFrontendDependenciesProvider is deprecated.');
            return function (id, placeholders) {
                var _a;
                if (placeholders === void 0) { placeholders = {}; }
                var message = (_a = translations === null || translations === void 0 ? void 0 : translations.messages["jsmessages:".concat(id)]) !== null && _a !== void 0 ? _a : id;
                return Object.keys(placeholders).reduce(function (message, placeholderKey) {
                    return message
                        .replaceAll("{{ ".concat(placeholderKey, " }}"), String(placeholders[placeholderKey]))
                        .replaceAll(placeholderKey, String(placeholders[placeholderKey]));
                }, message);
            };
        }
        return function () { return ''; };
    }), translator = _e[0], setTranslator = _e[1];
    var generateUrl = useCallback(function (route, parameters) {
        var routeConf = routes[route];
        if (undefined === routeConf) {
            throw new Error("Route ".concat(route, " not found"));
        }
        var queryString = createQueryParam(parameters);
        return (routeConf.tokens
            .map(function (token) {
            switch (token[0]) {
                case 'text':
                    return token[1];
                case 'variable':
                    if (parameters === undefined) {
                        throw new Error("Missing parameter: ".concat(token[3]));
                    }
                    return token[1] + parameters[token[3]];
                default:
                    throw new Error("Unexpected token type: ".concat(token[0]));
            }
        })
            .reverse()
            .join('') + queryString);
    }, [routes]);
    var securityContextUrl = generateUrl('pim_user_security_rest_get');
    var view = {
        setElement: function () { return view; },
        render: function () { },
        remove: function () { },
        setData: function () { },
    };
    useEffect(function () {
        var fetchSecurityContext = function () { return __awaiter(void 0, void 0, void 0, function () {
            var json;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4, fetcher(securityContextUrl)];
                    case 1:
                        json = _a.sent();
                        if (isMounted()) {
                            setSecurityContext(json);
                        }
                        return [2];
                }
            });
        }); };
        fetchSecurityContext();
        LegacyUserContext.initialize().then(function () {
            setUserContext(LegacyUserContext);
            if (translations !== undefined) {
                return;
            }
            initTranslator.fetch().then(function () { return setTranslator(function () { return translate; }); });
        });
    }, [securityContextUrl, isMounted]);
    var dependencies = useMemo(function () { return ({
        notify: notify,
        user: userContext,
        security: { isGranted: function (acl) { return securityContext[acl] === true; } },
        router: {
            generate: generateUrl,
            redirect: function (_fragment, _options) { return console.info('Not implemented'); },
            redirectToRoute: function (_route, _parameters) { return console.info('Not implemented'); },
        },
        translate: translator,
        viewBuilder: {
            build: function (_viewName) { return __awaiter(void 0, void 0, void 0, function () { return __generator(this, function (_a) {
                return [2, view];
            }); }); },
        },
        mediator: {
            trigger: function (event, _options) { return console.log('Triggering', event); },
            on: function (_event, _callback) { },
            off: function (_event, _callback) { },
        },
        featureFlags: {
            isEnabled: function () { return false; },
        },
        analytics: {
            track: function (event, properties) { return console.log('Track event', event, properties); },
            appcuesTrack: function (event, properties) { return console.log('Track event', event, properties); },
        },
    }); }, [notify, userContext, securityContext, translations, generateUrl, translator]);
    return (React.createElement(DependenciesContext.Provider, { value: dependencies },
        React.createElement(Notifications, { notifications: notifications, onNotificationClosed: handleNotificationClose }),
        children));
};
export { MicroFrontendDependenciesProvider };
//# sourceMappingURL=MicroFrontendDependenciesProvider.js.map