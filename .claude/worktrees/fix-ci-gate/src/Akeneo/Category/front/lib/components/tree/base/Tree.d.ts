import React, { PropsWithChildren, ReactNode, SyntheticEvent } from 'react';
import { PlaceholderPosition, TreeNode } from '../../../models';
export declare type CursorPosition = {
    x: number;
    y: number;
};
declare type TreeProps<T> = {
    value: TreeNode<T>;
    label: string;
    isLeaf?: boolean;
    selected?: boolean;
    isLoading?: boolean;
    readOnly?: boolean;
    isOpen?: boolean;
    open?: () => void;
    close?: () => void;
    onChange?: (value: TreeNode<T>, checked: boolean, event: SyntheticEvent) => void;
    onClick?: (value: TreeNode<T>) => void;
    onDrop?: () => void;
    onDragStart?: () => void;
    onDragEnd?: () => void;
    onDragOver?: (target: Element, cursorPosition: CursorPosition) => void;
    onDragEnter?: () => void;
    onDragLeave?: () => void;
    _isRoot?: boolean;
    children?: ReactNode;
    disabled?: boolean;
    draggable?: boolean;
    placeholderPosition?: PlaceholderPosition;
};
declare const Tree: {
    <T>({ label, value, children, isLeaf, selected, isLoading, readOnly, isOpen, open, close, onChange, onClick, onDrop, onDragStart, onDragEnd, onDragOver, onDragEnter, onDragLeave, _isRoot, disabled, draggable, placeholderPosition, ...rest }: React.PropsWithChildren<TreeProps<T>>): JSX.Element;
    displayName: string;
    Actions: React.FC<{}>;
    Skeleton: React.FC<{}>;
};
export { Tree };
