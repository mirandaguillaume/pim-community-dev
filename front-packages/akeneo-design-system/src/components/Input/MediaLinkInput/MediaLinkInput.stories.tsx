import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {MediaLinkInput} from './MediaLinkInput.tsx';
import {FullscreenPreview} from '../../../storybook';
import {IconButton, Button} from '../../../components';
import {RefreshIcon, CopyIcon, DownloadIcon, FullscreenIcon} from '../../../icons';
import {useBooleanState} from '../../../hooks';

const meta: Meta<typeof MediaLinkInput> = {
  title: 'Components/Inputs/Media Link input',
  component: MediaLinkInput,
  args: {
    value: null,
    placeholder: 'Put your media link here',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    <>
          <MediaLinkInput {...args} value={value} onChange={setValue} thumbnailUrl={value}>
            <IconButton icon={<RefreshIcon />} title="Regenerate" onClick={console.log} />
            <IconButton icon={<CopyIcon />} title="Copy" onClick={console.log} />
            <IconButton href={value} target="_blank" download={value} icon={<DownloadIcon />} title="Download" />
            <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
          </MediaLinkInput>
          {isFullscreenModalOpen && value && (
            <FullscreenPreview title={value.originalFilename} src={value} onClose={closeFullscreenModal}>
              <Button href={value} ghost={true} level="tertiary" target="_blank" download={value}>
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  },
};

export const ReadOnly: Story = {
  name: 'Read only',
  render: (args) => {
    <>
          <MediaLinkInput {...args} readOnly={false} value="" onChange={setValue} thumbnailUrl={value}>
            <IconButton icon={<RefreshIcon />} title="Regenerate" onClick={console.log} />
            <IconButton icon={<CopyIcon />} title="Copy" onClick={console.log} />
            <IconButton href={value} target="_blank" download={value} icon={<DownloadIcon />} title="Download" />
            <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
          </MediaLinkInput>
          <MediaLinkInput {...args} readOnly={true} value="" onChange={setValue} thumbnailUrl={value}>
            <IconButton icon={<RefreshIcon />} title="Regenerate" onClick={console.log} />
            <IconButton icon={<CopyIcon />} title="Copy" onClick={console.log} />
            <IconButton href={value} target="_blank" download={value} icon={<DownloadIcon />} title="Download" />
            <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
          </MediaLinkInput>
          <MediaLinkInput {...args} readOnly={true} value={value} onChange={setValue} thumbnailUrl={value}>
            <IconButton icon={<RefreshIcon />} title="Regenerate" onClick={console.log} />
            <IconButton icon={<CopyIcon />} title="Copy" onClick={console.log} />
            <IconButton href={value} target="_blank" download={value} icon={<DownloadIcon />} title="Download" />
            <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
          </MediaLinkInput>
          {isFullscreenModalOpen && value && (
            <FullscreenPreview title={value.originalFilename} src={value} onClose={closeFullscreenModal}>
              <Button href={value} ghost={true} level="tertiary" target="_blank" download={value}>
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  },
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => {
    <>
          <MediaLinkInput {...args} invalid={false} value={value} onChange={setValue} thumbnailUrl={value}>
            <IconButton icon={<RefreshIcon />} title="Regenerate" onClick={console.log} />
            <IconButton icon={<CopyIcon />} title="Copy" onClick={console.log} />
            <IconButton href={value} target="_blank" download={value} icon={<DownloadIcon />} title="Download" />
            <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
          </MediaLinkInput>
          <MediaLinkInput {...args} invalid={true} value={value} onChange={setValue} thumbnailUrl={value}>
            <IconButton icon={<RefreshIcon />} title="Regenerate" onClick={console.log} />
            <IconButton icon={<CopyIcon />} title="Copy" onClick={console.log} />
            <IconButton href={value} target="_blank" download={value} icon={<DownloadIcon />} title="Download" />
            <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
          </MediaLinkInput>
          {isFullscreenModalOpen && value && (
            <FullscreenPreview title={value.originalFilename} src={value} onClose={closeFullscreenModal}>
              <Button href={value} ghost={true} level="tertiary" target="_blank" download={value}>
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  },
};

