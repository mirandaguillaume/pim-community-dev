declare type ConfigContextValue = {
    operations_max: number;
    units_max: number;
    families_max: number;
};
declare const ConfigContext: import("react").Context<ConfigContextValue>;
export { ConfigContext };
export type { ConfigContextValue };
