import React, { FC } from 'react';
import { DraggedNode, DropTarget, HoveredNode } from '../../models';
declare type OrderableTreeState = {
    draggedNode: DraggedNode | null;
    setDraggedNode: (data: DraggedNode | null) => void;
    endMove: () => void;
    isActive: boolean;
};
declare const OrderableTreeContext: React.Context<OrderableTreeState>;
declare type Props = {
    isActive: boolean;
};
declare const OrderableTreeProvider: FC<Props>;
export type { DraggedNode, HoveredNode, DropTarget, OrderableTreeState };
export { OrderableTreeProvider, OrderableTreeContext };
