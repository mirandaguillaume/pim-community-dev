import React, {useState} from 'react';
import styled from 'styled-components';
import {Editor} from '@tiptap/react';
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

const RichTextEditorToolbar = ({editor}: {editor: Editor}) => {
  const [linkFormOpen, setLinkFormOpen] = useState(false);
  const [href, setHref] = useState('');

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
          active={editor.isActive('bold')}
          onClick={() => editor.chain().focus().toggleBold().run()}
          title="Bold"
        >
          B
        </Button>
        <Button
          level="tertiary"
          ghost
          size="small"
          active={editor.isActive('italic')}
          onClick={() => editor.chain().focus().toggleItalic().run()}
          title="Italic"
        >
          I
        </Button>
      </Group>

      <Group aria-label="block-type-control">
        <NarrowSelect>
          <SelectInput
            emptyResultLabel="No result"
            openLabel="Open"
            value={currentBlockType(editor)}
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
            value={textStyleOf(editor).fontSize ?? null}
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
            value={textStyleOf(editor).fontFamily ?? null}
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
          active={editor.isActive('bulletList')}
          onClick={() => editor.chain().focus().toggleBulletList().run()}
          title="Bulleted list"
        >
          &bull;
        </Button>
        <Button
          level="tertiary"
          ghost
          size="small"
          active={editor.isActive('orderedList')}
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
            active={editor.isActive('link')}
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
