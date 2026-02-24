import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {MediaFileInput} from './MediaFileInput.tsx';
import {useFakeMediaStorage, FullscreenPreview} from '../../../storybook';
import {IconButton, Button} from '../../../components';
import {DownloadIcon, FullscreenIcon} from '../../../icons';
import {useBooleanState} from '../../../hooks';

const meta: Meta<typeof MediaFileInput> = {
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

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    <>
          <MediaFileInput
            {...args}
            value={value}
            onChange={setValue}
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
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  },
};

export const Size: Story = {
  name: 'Size',
  render: (args) => {
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
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  },
};

export const Clearable: Story = {
  name: 'Clearable',
  render: (args) => {
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
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  },
};

export const ReadOnly: Story = {
  name: 'Read only',
  render: (args) => (
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
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  ),
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => {
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
                <DownloadIcon /> Download
              </Button>
            </FullscreenPreview>
          )}
        </>
  },
};

