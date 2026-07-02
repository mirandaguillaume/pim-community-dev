import { useDependenciesContext } from './useDependenciesContext';
var useSystemConfiguration = function () {
    var systemConfiguration = useDependenciesContext().systemConfiguration;
    if (!systemConfiguration) {
        throw new Error('[DependenciesContext]: SystemConfiguration has not been properly initiated');
    }
    return systemConfiguration;
};
export { useSystemConfiguration };
//# sourceMappingURL=useSystemConfiguration.js.map