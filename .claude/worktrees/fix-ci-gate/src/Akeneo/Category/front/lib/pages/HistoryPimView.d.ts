import { View } from '@akeneo-pim-community/shared';
declare type Props = {
    viewName: string;
    className?: string;
    onBuild?: (view: View) => Promise<View | null>;
    version: number;
};
declare const HistoryPimView: ({ viewName, className, onBuild, version }: Props) => JSX.Element;
export type { View };
export { HistoryPimView };
