import { useDependenciesContext } from './useDependenciesContext';
var useFeatureFlags = function () {
    var featureFlags = useDependenciesContext().featureFlags;
    if (!featureFlags) {
        throw new Error('[DependenciesContext]: FeatureFlags has not been properly initiated');
    }
    return featureFlags;
};
export { useFeatureFlags };
//# sourceMappingURL=useFeatureFlags.js.map