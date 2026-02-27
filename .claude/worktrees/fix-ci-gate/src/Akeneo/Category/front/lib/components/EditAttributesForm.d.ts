import { Attribute, CategoryAttributeValueData, EnrichCategory, Template } from '../models';
interface Props {
    attributeValues: EnrichCategory['attributes'];
    template: Template;
    onAttributeValueChange: (attribute: Attribute, channel: string | null, locale: string | null, attributeValue: CategoryAttributeValueData) => void;
    onLocaleChange?: (locale: string) => void;
}
export declare const EditAttributesForm: ({ attributeValues, template, onAttributeValueChange, onLocaleChange }: Props) => JSX.Element;
export {};
