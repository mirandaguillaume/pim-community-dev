import { LocaleCode, ChannelCode } from '@akeneo-pim-community/shared';
import { Attribute, CategoryAttributes, CategoryAttributeValueData, CategoryImageAttributeValueData, EnrichCategory, File, Template } from '../models';
import { CategoryPermissions } from '../models/CategoryPermission';
export declare function permissionsAreEqual(cp1: CategoryPermissions, cp2: CategoryPermissions): boolean;
export declare function categoriesAreEqual(c1: EnrichCategory, c2: EnrichCategory): boolean;
export declare function getAttributeValue(attributes: CategoryAttributes, attribute: Attribute, channelCode: ChannelCode, localeCode: LocaleCode): CategoryAttributeValueData | undefined;
export declare function populateCategory(category: EnrichCategory, template: Template | null | undefined, channels: ChannelCode[], locales: LocaleCode[]): EnrichCategory;
export declare const convertCategoryImageAttributeValueDataToFileInfo: (valueData: CategoryImageAttributeValueData | null) => File;
export declare const convertFileInfoToCategoryImageAttributeValueData: (value: File) => CategoryImageAttributeValueData | null;
