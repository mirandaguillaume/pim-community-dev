import React, {useState} from 'react';
import styled from 'styled-components';
import {Editor, useEditorState} from '@tiptap/react';
import {Button} from '../../Button/Button';
import {SelectInput} from '../SelectInput/SelectInput';
import {TextInput} from '../TextInput/TextInput';
import {getColor} from '../../../theme';

const Container = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  padding: 8px;
  border-bottom: 1px solid ${getColor('grey', 80)};
`;

const Group = styled.div`
  display: flex;
  gap: 4px;
  align-items: center;
`;

const NarrowSelect = styled.div`
  width: 140px;
`;

const LinkForm = styled.div`
  display: flex;
  gap: 4px;
  align-items: center;
  width: 260px;
`;

const BLOCK_TYPES = [
  {value: 'paragraph', label: 'Normal'},
  {value: '1', label: 'Title 1'},
  {value: '2', label: 'Title 2'},
  {value: '3', label: 'Title 3'},
];

const FONT_SIZES = ['11px', '12px', '14px', '16px', '18px', '24px', '30px', '36px'];

const FONT_FAMILIES = ['Arial', 'Georgia', 'Tahoma', 'Times New Roman', 'Verdana'];

// getAttributes rend un Record<string, any> : on fixe la forme qu'on y lit plutot que de
// propager un any dans le JSX.
type TextStyleAttributes = {fontSize?: string; fontFamily?: string};

const textStyleOf = (editor: Editor): TextStyleAttributes => editor.getAttributes('textStyle') as TextStyleAttributes;

const currentBlockType = (editor: Editor): string => {
  for (const {value} of BLOCK_TYPES) {
    if ('paragraph' !== value && editor.isActive('heading', {level: Number(value)})) return value;
  }

  return 'paragraph';
};

// Since Tiptap 3, useEditor no longer re-renders on every transaction (shouldRerenderOnTransaction
// defaults to false), so reading editor.isActive() during render froze the toolbar on its first
// state: picking "Title 2" did turn the block into an <h2>, yet the select kept showing "Normal".
// Subscribing here re-renders the toolbar only when one of these displayed values changes, rather
// than re-rendering the whole editor on every keystroke as the v2 default did.
const selectToolbarState = ({editor}: {editor: Editor}) => ({
  isBold: editor.isActive('bold'),
  isItalic: editor.isActive('italic'),
  isBulletList: editor.isActive('bulletList'),
  isOrderedList: editor.isActive('orderedList'),
  isLink: editor.isActive('link'),
  blockType: currentBlockType(editor),
  fontSize: textStyleOf(editor).fontSize ?? null,
  fontFamily: textStyleOf(editor).fontFamily ?? null,
});

const RichTextEditorToolbar = ({editor}: {editor: Editor}) => {
  const [linkFormOpen, setLinkFormOpen] = useState(false);
  const [href, setHref] = useState('');
  const state = useEditorState({editor, selector: selectToolbarState});

  const setBlockType = (value: string) => {
    if ('paragraph' === value) {
      editor.chain().focus().setParagraph().run();
      return;
    }

    editor
      .chain()
      .focus()
      .setHeading({level: Number(value) as 1 | 2 | 3})
      .run();
  };

  const applyLink = () => {
    const chain = editor.chain().focus().extendMarkRange('link');

    '' === href ? chain.unsetLink().run() : chain.setLink({href}).run();
    setLinkFormOpen(false);
    setHref('');
  };

  return (
    <Container role="toolbar" aria-label="Rich text controls">
      <Group aria-label="inline-control">
        <Button
          level="tertiary"
          ghost
          size="small"
          active={state.isBold}
          onClick={() => editor.chain().focus().toggleBold().run()}
          title="Bold"
        >
          B
        </Button>
        <Button
          level="tertiary"
          ghost
          size="small"
          active={state.isItalic}
          onClick={() => editor.chain().focus().toggleItalic().run()}
          title="Italic"
        >
          I
        </Button>
      </Group>

      <Group aria-label="block-type-control">
        <NarrowSelect>
          {/* Not clearable: a block always has a type, and clearing would hand null to setHeading.
              The clear button also had no accessible name (clearLabel defaults to ''). */}
          <SelectInput
            emptyResultLabel="No result"
            openLabel="Open"
            placeholder="Style"
            clearable={false}
            value={state.blockType}
            onChange={setBlockType}
          >
            {BLOCK_TYPES.map(({value, label}) => (
              <SelectInput.Option key={value} value={value}>
                {label}
              </SelectInput.Option>
            ))}
          </SelectInput>
        </NarrowSelect>
      </Group>

      <Group aria-label="font-size-control">
        <NarrowSelect>
          <SelectInput
            emptyResultLabel="No result"
            openLabel="Open"
            placeholder="Size"
            clearable={false}
            value={state.fontSize}
            onChange={(size: string) => editor.chain().focus().setFontSize(size).run()}
          >
            {FONT_SIZES.map(size => (
              <SelectInput.Option key={size} value={size}>
                {size.replace('px', '')}
              </SelectInput.Option>
            ))}
          </SelectInput>
        </NarrowSelect>
      </Group>

      <Group aria-label="font-family-control">
        <NarrowSelect>
          <SelectInput
            emptyResultLabel="No result"
            openLabel="Open"
            placeholder="Font"
            clearable={false}
            value={state.fontFamily}
            onChange={(font: string) => editor.chain().focus().setFontFamily(font).run()}
          >
            {FONT_FAMILIES.map(font => (
              <SelectInput.Option key={font} value={font}>
                {font}
              </SelectInput.Option>
            ))}
          </SelectInput>
        </NarrowSelect>
      </Group>

      <Group aria-label="list-control">
        <Button
          level="tertiary"
          ghost
          size="small"
          active={state.isBulletList}
          onClick={() => editor.chain().focus().toggleBulletList().run()}
          title="Bulleted list"
        >
          &bull;
        </Button>
        <Button
          level="tertiary"
          ghost
          size="small"
          active={state.isOrderedList}
          onClick={() => editor.chain().focus().toggleOrderedList().run()}
          title="Numbered list"
        >
          1.
        </Button>
      </Group>

      <Group aria-label="link-control">
        {linkFormOpen ? (
          <LinkForm>
            <TextInput value={href} onChange={setHref} placeholder="https://" aria-label="Link URL" />
            <Button level="tertiary" size="small" onClick={applyLink}>
              Apply
            </Button>
          </LinkForm>
        ) : (
          <Button
            level="tertiary"
            ghost
            size="small"
            active={state.isLink}
            onClick={() => {
              setHref(editor.getAttributes('link').href ?? '');
              setLinkFormOpen(true);
            }}
            title="Link"
          >
            Link
          </Button>
        )}
      </Group>

      <Group aria-label="clear-formatting-control">
        <Button
          level="tertiary"
          ghost
          size="small"
          onClick={() => editor.chain().focus().unsetAllMarks().clearNodes().run()}
          title="Clear formatting"
        >
          Clear
        </Button>
      </Group>
    </Container>
  );
};

export {RichTextEditorToolbar};
