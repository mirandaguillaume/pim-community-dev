import { LocaleCode, ChannelCode } from '@akeneo-pim-community/shared';
import { Attribute } from './Attribute';
export declare type CompositeKey = string;
export declare type CompositeKeyWithoutLocale = string;
export declare function buildCompositeKey(attribute: Attribute, channelCode?: ChannelCode | null, localeCode?: LocaleCode | null): CompositeKey;
