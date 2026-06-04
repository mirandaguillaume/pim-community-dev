import React, {useState} from 'react';
import {MediaLinkInput} from './MediaLinkInput';
import {FullscreenPreview} from '../../../storybook';
import {IconButton, Button} from '../../../components';
import {RefreshIcon, CopyIcon, DownloadIcon, FullscreenIcon} from '../../../icons';
import {useBooleanState} from '../../../hooks';

export default {
  title: 'Components/Inputs/Media Link input',
  component: MediaLinkInput,

  args: {
    value: null,
    placeholder: 'Put your media link here',
  },
};

export const Standard = {
  render: args => {
    const [value, setValue] = useState('https://picsum.photos/seed/strixos/500/500');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();

    return (
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
              <DownloadIcon />
              Download
            </Button>
          </FullscreenPreview>
        )}
      </>
    );
  },

  name: 'Standard',
};

export const ReadOnly = {
  render: args => {
    const [value, setValue] = useState('https://picsum.photos/seed/strixos/500/500');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();

    return (
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
              <DownloadIcon />
              Download
            </Button>
          </FullscreenPreview>
        )}
      </>
    );
  },

  name: 'Read only',
};

export const Invalid = {
  render: args => {
    const [value, setValue] = useState('https://picsum.photos/seed/strixos/500/500');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();

    return (
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
              <DownloadIcon />
              Download
            </Button>
          </FullscreenPreview>
        )}
      </>
    );
  },

  name: 'Invalid',
};
