declare type Props = {
    templateUuid: string;
    attribute: {
        uuid: string;
        label: string;
    };
    onClose: () => void;
};
export declare const DeactivateTemplateAttributeModal: ({ templateUuid, attribute, onClose }: Props) => JSX.Element;
export {};
