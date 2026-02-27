declare type Props = {
    template: {
        id: string;
        label: string;
    };
    onClose: () => void;
};
export declare const DeactivateTemplateModal: ({ template, onClose }: Props) => JSX.Element;
export {};
