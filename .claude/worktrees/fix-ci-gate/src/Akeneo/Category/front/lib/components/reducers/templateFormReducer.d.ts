export declare type TemplateFormState = {
    attributes: {
        [uuid: string]: {
            labels: {
                [localeCode: string]: {
                    value: string;
                    errors: string[];
                };
            };
        };
    };
    properties: {
        labels: {
            [localeCode: string]: {
                value: string;
                errors: string[];
            };
        };
    };
};
export declare type TemplateFormAction = {
    type: 'attribute_label_translation_changed';
    payload: {
        attributeUuid: string;
        localeCode: string;
        value: string;
    };
} | {
    type: 'attribute_label_translation_saved';
    payload: {
        attributeUuid: string;
        localeCode: string;
        value: string;
    };
} | {
    type: 'save_attribute_label_translation_failed';
    payload: {
        attributeUuid: string;
        localeCode: string;
        errors: string[];
    };
} | {
    type: 'template_label_translation_changed';
    payload: {
        localeCode: string;
        value: string;
    };
} | {
    type: 'template_label_translation_saved';
    payload: {
        localeCode: string;
        value: string;
    };
} | {
    type: 'save_template_label_translation_failed';
    payload: {
        localeCode: string;
        errors: string[];
    };
};
export declare const templateFormReducer: (previousState: TemplateFormState, action: TemplateFormAction) => TemplateFormState;
