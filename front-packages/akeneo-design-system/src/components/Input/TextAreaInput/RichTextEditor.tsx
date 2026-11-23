import React, {useEffect} from 'react';
import {Editor, EditorContent, useEditor} from '@tiptap/react';
import {StarterKit} from '@tiptap/starter-kit';
import {FontFamily, FontSize, TextStyle} from '@tiptap/extension-text-style';
import {RichTextEditorToolbar} from './RichTextEditorToolbar';

type RichTextEditorProps = {
  value: string;
  readOnly?: boolean;
  onChange: (value: string) => void;
};

// Tiptap stores a document, not a string, so "controlled" has to be built: push `value` in only when
// it actually differs from what the editor holds. Comparing is what keeps the caret still -- on every
// keystroke onUpdate has already reported this exact HTML back, so the two are equal and nothing is
// pushed; when a consumer swaps locale or channel they differ and the document is replaced.
//
// The editor this replaced never pushed anything after mount, which is why its consumer in the
// Category workspace had to force a remount through a composite React key. That key is gone.
const useExternalValue = (editor: Editor | null, value: string) => {
  useEffect(() => {
    if (null !== editor && value !== editor.getHTML()) {
      editor.commands.setContent(value, {emitUpdate: false});
    }
  }, [editor, value]);
};

const RichTextEditor = ({value, readOnly = false, onChange}: RichTextEditorProps) => {
  const editor = useEditor({
    // Bold, Italic, Link, the lists and the headings all come from StarterKit; only the two
    // text-style marks need naming, and both require TextStyle alongside them.
    extensions: [StarterKit, TextStyle, FontSize, FontFamily],
    content: value,
    editable: !readOnly,
    onUpdate: ({editor}) => onChange(editor.getHTML()),
  });

  useExternalValue(editor, value);

  useEffect(() => {
    editor?.setEditable(!readOnly);
  }, [editor, readOnly]);

  return (
    <>
      {!readOnly && null !== editor && <RichTextEditorToolbar editor={editor} />}
      <EditorContent editor={editor} />
    </>
  );
};

export {RichTextEditor};
export type {RichTextEditorProps};
