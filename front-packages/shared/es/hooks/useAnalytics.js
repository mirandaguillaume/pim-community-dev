import { useDependenciesContext } from './useDependenciesContext';
var useAnalytics = function () {
    var analytics = useDependenciesContext().analytics;
    if (!analytics) {
        throw new Error('[DependenciesContext]: Analytics has not been properly initiated');
    }
    return analytics;
};
export { useAnalytics };
//# sourceMappingURL=useAnalytics.js.map