import React, {useState} from 'react';
import {Editor, EditorProps} from 'react-draft-wysiwyg';
import draftToHtml from 'draftjs-to-html';
import htmlToDraft from 'html-to-draftjs';
import {ContentState, convertToRaw, EditorState} from 'draft-js';
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';
import {Override} from '../../../shared';

const editorStateToRaw = (editorState: EditorState): string =>
  draftToHtml(convertToRaw(editorState.getCurrentContent()));

const rawToEditorState = (value: string): EditorState => {
  const rawDraft = htmlToDraft(value);

  if (!rawDraft || !rawDraft.contentBlocks) {
    return EditorState.createEmpty();
  }

  return EditorState.createWithContent(ContentState.createFromBlockArray(rawDraft.contentBlocks));
};

type RichTextEditorProps = Override<
  EditorProps,
  {
    value: string;
    readOnly?: boolean;
    onChange: (value: string) => void;
  }
>;

const RichTextEditor = ({value, readOnly = false, onChange, ...rest}: RichTextEditorProps) => {
  const [editorState, setEditorState] = useState<EditorState>(rawToEditorState(value));

  const handleChange = (editorState: EditorState) => {
    setEditorState(editorState);
    onChange(editorStateToRaw(editorState));
  };

  return (
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-expect-error — react-draft-wysiwyg bundles @types/react 17 which conflicts with React 18 types
    <Editor
      toolbarHidden={readOnly}
      readOnly={readOnly}
      toolbar={{
        // 'embedded' and 'image' are deliberately absent, and must not be re-added without reading
        // the tests that pin this list. Two of them: one in TextAreaInput.unit.tsx next door, and
        // one in the Category workspace's buildRichTextFieldAttribute.test.tsx. The second is the
        // one that matters, because no CI job runs this package's own unit tests.
        //
        // react-draft-wysiwyg carries an unpatched cross-site scripting flaw reached through the
        // embedded button, and no corrected release exists -- npm reports the patched range as
        // `<0.0.0`, which is its way of saying the package is unmaintained. Dropping the button is
        // what makes that flaw unreachable through our configuration; a version bump cannot.
        //
        // 'image' goes with it for a different reason: it let arbitrary markup into the stored HTML
        // for a capability nothing depends on -- a category carries its pictures through its own
        // `image` attribute type. That half is surface reduction, not a fix, and can be argued
        // separately from the one above.
        options: ['inline', 'blockType', 'fontSize', 'fontFamily', 'list', 'link', 'remove'],
        inline: {
          options: ['bold', 'italic'],
        },
      }}
      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
      onEditorStateChange={handleChange as any}
      {...rest}
      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
      editorState={editorState as any}
    />
  );
};

export {RichTextEditor};
export type {EditorProps};
