import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {TemplateFormProvider, useTemplateForm} from './TemplateFormProvider';

const createWrapper =
  () =>
  ({children}: {children: React.ReactNode}) =>
    React.createElement(TemplateFormProvider, null, children);

describe('useTemplateForm', () => {
  it('throws when used outside TemplateFormProvider', () => {
    expect(() => renderHook(() => useTemplateForm())).toThrow(
      'useTemplateForm must be used within a TemplateFormProvider'
    );
  });

  it('returns initial state with empty attributes and labels', () => {
    const {result} = renderHook(() => useTemplateForm(), {wrapper: createWrapper()});
    const [state] = result.current;
    expect(state).toEqual({attributes: {}, properties: {labels: {}}});
  });

  it('provides a dispatch function as the second element', () => {
    const {result} = renderHook(() => useTemplateForm(), {wrapper: createWrapper()});
    const [, dispatch] = result.current;
    expect(typeof dispatch).toBe('function');
  });

  it('updates properties.labels after dispatching template_label_translation_changed', () => {
    const {result} = renderHook(() => useTemplateForm(), {wrapper: createWrapper()});

    act(() => {
      result.current[1]({
        type: 'template_label_translation_changed',
        payload: {localeCode: 'en_US', value: 'My Template'},
      });
    });

    const [state] = result.current;
    expect(state.properties.labels['en_US']).toEqual({value: 'My Template', errors: []});
  });
});
