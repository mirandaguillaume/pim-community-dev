import { Attribute } from '../../../models';
declare type Props = {
    attributes: Attribute[];
    selectedAttribute: Attribute;
    templateId: string;
    onAttributeSelection: (attribute: Attribute) => void;
};
export declare const AttributeList: ({ attributes, selectedAttribute, templateId, onAttributeSelection }: Props) => JSX.Element;
export {};
