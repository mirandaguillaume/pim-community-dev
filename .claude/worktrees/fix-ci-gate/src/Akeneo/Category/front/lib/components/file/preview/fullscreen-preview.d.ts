import { Attribute } from '../../../models';
import { File } from '../../../models';
declare type FullscreenPreviewProps = {
    label: string;
    attribute: Attribute;
    data: File;
    onClose: () => void;
};
declare const FullscreenPreview: ({ label, data, attribute, onClose }: FullscreenPreviewProps) => JSX.Element;
export { FullscreenPreview };
