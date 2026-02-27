export interface ScopedValue<ValueType> {
    value: ValueType;
    scope: 'app';
    use_parent_scope_value: boolean;
}
declare type BooleanBackend = string | boolean | null;
export interface ConfigServicePayloadBackend {
    pim_ui___language: ScopedValue<string>;
    pim_analytics___version_update: ScopedValue<BooleanBackend>;
    pim_ui___loading_message_enabled: ScopedValue<BooleanBackend>;
    pim_ui___loading_messages: ScopedValue<string>;
    pim_ui___sandbox_banner: ScopedValue<BooleanBackend>;
}
export interface ConfigServicePayloadFrontend {
    pim_ui___language: ScopedValue<string>;
    pim_analytics___version_update: ScopedValue<boolean>;
    pim_ui___loading_message_enabled: ScopedValue<boolean>;
    pim_ui___loading_messages: ScopedValue<string>;
    pim_ui___sandbox_banner: ScopedValue<boolean>;
}
export declare function configFrontToBack(config: ConfigServicePayloadFrontend): ConfigServicePayloadBackend;
export declare function configBackToFront(config: ConfigServicePayloadBackend): ConfigServicePayloadFrontend;
export {};
