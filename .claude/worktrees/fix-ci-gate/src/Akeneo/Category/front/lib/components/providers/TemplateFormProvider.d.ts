import React from 'react';
import { TemplateFormAction, TemplateFormState } from '../reducers/templateFormReducer';
export declare const useTemplateForm: () => [TemplateFormState, React.Dispatch<TemplateFormAction>];
declare type Props = {
    children: React.ReactNode;
};
export declare const TemplateFormProvider: ({ children }: Props) => JSX.Element;
export {};
