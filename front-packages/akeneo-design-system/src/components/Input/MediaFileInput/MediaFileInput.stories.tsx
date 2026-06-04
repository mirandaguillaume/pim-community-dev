import React, {useState} from 'react';
import {MediaFileInput} from './MediaFileInput';
import {useFakeMediaStorage, FullscreenPreview} from '../../../storybook';
import {IconButton, Button} from '../../../components';
import {DownloadIcon, FullscreenIcon} from '../../../icons';
import {useBooleanState} from '../../../hooks';

export default {
  title: 'Components/Inputs/Media File input',
  component: MediaFileInput,

  args: {
    value: null,
    placeholder: 'Drag and drop to upload or click here',
    clearTitle: 'Clear',
    uploadingLabel: 'Uploading...',
    uploadErrorLabel: 'An error occurred during upload',
  },
};

export const Standard = {
  render: args => {
    const [thumbnailUrl, uploader] = useFakeMediaStorage('https://picsum.photos/seed/akeneo/200');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
    const [value, setValue] = useState(null);

    return (
      <>
        <MediaFileInput {...args} value={value} onChange={setValue} thumbnailUrl={thumbnailUrl} uploader={uploader}>
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        {isFullscreenModalOpen && value && (
          <FullscreenPreview title={value.originalFilename} src={thumbnailUrl} onClose={closeFullscreenModal}>
            <Button href={thumbnailUrl} ghost={true} level="tertiary" target="_blank" download={thumbnailUrl}>
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

export const Size = {
  render: args => {
    const file = {
      filePath: '/file/path.jpg',
      originalFilename: 'nice-name.jpg',
    };

    const [thumbnailUrl, uploader] = useFakeMediaStorage('https://picsum.photos/seed/akeneo/200');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
    const [value, setValue] = useState(file);

    return (
      <>
        <MediaFileInput {...args} value={value} thumbnailUrl={thumbnailUrl} onChange={setValue} uploader={uploader}>
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        <MediaFileInput
          {...args}
          size="small"
          value={value}
          thumbnailUrl={thumbnailUrl}
          onChange={setValue}
          uploader={uploader}
        >
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        {isFullscreenModalOpen && value && (
          <FullscreenPreview title={value.originalFilename} src={thumbnailUrl} onClose={closeFullscreenModal}>
            <Button href={thumbnailUrl} ghost={true} level="tertiary" target="_blank" download={thumbnailUrl}>
              <DownloadIcon />
              Download
            </Button>
          </FullscreenPreview>
        )}
      </>
    );
  },

  name: 'Size',
};

export const Clearable = {
  render: args => {
    const file = {
      filePath: '/file/path.jpg',
      originalFilename: 'nice-name.jpg',
    };

    const [thumbnailUrl, uploader] = useFakeMediaStorage('https://picsum.photos/seed/akeneo/200');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
    const [value, setValue] = useState(file);

    return (
      <>
        <MediaFileInput
          {...args}
          size="small"
          value={value}
          thumbnailUrl={thumbnailUrl}
          onChange={setValue}
          uploader={uploader}
          clearable={true}
        >
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        <MediaFileInput
          {...args}
          size="small"
          value={value}
          thumbnailUrl={thumbnailUrl}
          onChange={setValue}
          uploader={uploader}
          clearable={false}
        >
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        {isFullscreenModalOpen && value && (
          <FullscreenPreview title={value.originalFilename} src={thumbnailUrl} onClose={closeFullscreenModal}>
            <Button href={thumbnailUrl} ghost={true} level="tertiary" target="_blank" download={thumbnailUrl}>
              <DownloadIcon />
              Download
            </Button>
          </FullscreenPreview>
        )}
      </>
    );
  },

  name: 'Clearable',
};

export const ReadOnly = {
  render: args => {
    const file = {
      filePath: '/file/path.jpg',
      originalFilename: 'nice-name.jpg',
    };

    const [thumbnailUrl, uploader] = useFakeMediaStorage('https://picsum.photos/seed/akeneo/200');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();

    return (
      <>
        <MediaFileInput {...args} value={null} readOnly uploader={uploader} thumbnailUrl={thumbnailUrl}>
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        <MediaFileInput {...args} value={file} readOnly uploader={uploader} thumbnailUrl={thumbnailUrl}>
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        <MediaFileInput {...args} value={null} size="small" readOnly uploader={uploader} thumbnailUrl={thumbnailUrl}>
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        <MediaFileInput {...args} value={file} size="small" readOnly uploader={uploader} thumbnailUrl={thumbnailUrl}>
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        {isFullscreenModalOpen && file && (
          <FullscreenPreview title={file.originalFilename} src={thumbnailUrl} onClose={closeFullscreenModal}>
            <Button href={thumbnailUrl} ghost={true} level="tertiary" target="_blank" download={thumbnailUrl}>
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
    const file = {
      filePath: '/file/path.jpg',
      originalFilename: 'invalid-image.jpg',
    };

    const [thumbnailUrl, uploader] = useFakeMediaStorage('https://picsum.photos/seed/akeneo/200');
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
    const [value, setValue] = useState(file);

    return (
      <>
        <MediaFileInput
          {...args}
          value={value}
          thumbnailUrl={thumbnailUrl}
          onChange={setValue}
          uploader={uploader}
          invalid={true}
        >
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        <MediaFileInput
          {...args}
          size="small"
          value={value}
          thumbnailUrl={thumbnailUrl}
          onChange={setValue}
          uploader={uploader}
          invalid={true}
        >
          <IconButton
            href={thumbnailUrl}
            target="_blank"
            download={thumbnailUrl}
            icon={<DownloadIcon />}
            title="Download"
          />
          <IconButton icon={<FullscreenIcon />} title="Fullscreen" onClick={openFullscreenModal} />
        </MediaFileInput>
        {isFullscreenModalOpen && value && (
          <FullscreenPreview title={value.originalFilename} src={thumbnailUrl} onClose={closeFullscreenModal}>
            <Button href={thumbnailUrl} ghost={true} level="tertiary" target="_blank" download={thumbnailUrl}>
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
