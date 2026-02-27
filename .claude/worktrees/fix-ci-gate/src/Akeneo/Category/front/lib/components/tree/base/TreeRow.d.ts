import { PlaceholderPosition } from '../../../models';
declare type TreeRowProps = {
    $selected: boolean;
    $disabled: boolean;
    isRoot: boolean;
    placeholderPosition?: PlaceholderPosition;
};
declare const TreeRow: import("styled-components").StyledComponent<"div", any, TreeRowProps & Record<string, unknown> & import("styled-components").ThemeProps<import("akeneo-design-system/lib/theme/theme").Theme>, never>;
declare const ArrowButton: import("styled-components").StyledComponent<"button", any, {}, never>;
declare const TreeArrowIcon: any;
declare const RowInnerContainer: import("styled-components").StyledComponent<"div", any, {}, never>;
declare const RowActionsContainer: import("styled-components").StyledComponent<"div", any, {}, never>;
declare const DragInitiator: import("styled-components").StyledComponent<"div", any, Record<string, unknown> & import("styled-components").ThemeProps<import("akeneo-design-system/lib/theme/theme").Theme>, never>;
export type { PlaceholderPosition };
export { TreeRow, TreeArrowIcon, ArrowButton, RowActionsContainer, RowInnerContainer, DragInitiator };
