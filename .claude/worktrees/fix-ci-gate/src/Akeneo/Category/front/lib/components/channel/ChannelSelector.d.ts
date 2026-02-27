import { Channel, ChannelCode } from '@akeneo-pim-community/shared';
declare type ChannelSelectorProps = {
    value: ChannelCode;
    values: Channel[];
    onChange: (value: ChannelCode) => void;
};
declare const ChannelSelector: ({ value, values, onChange }: ChannelSelectorProps) => JSX.Element;
export { ChannelSelector };
