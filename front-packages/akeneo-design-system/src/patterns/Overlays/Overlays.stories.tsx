import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {Content} from '../../storybook/PreviewGallery';
import {Button, Modal} from '../../components';
import {AddingValueIllustration, DeleteIllustration} from '../../illustrations';

const meta: Meta<typeof any> = {
  title: 'Patterns/Overlays',
};

export default meta;
type Story = StoryObj<typeof meta>;

export const ConfirmModal: Story = {
  name: 'Confirm Modal',
  render: (args) => {
    <>
          {isOpen && (
            <Modal closeTitle="Close" onClose={close} illustration={<DeleteIllustration />}>
              <Modal.SectionTitle color="brand">Products</Modal.SectionTitle>
              <Modal.Title>Confirm deletion</Modal.Title>
              Are you sure you want to delete this product?
              <Modal.BottomButtons>
                <Button level="tertiary" onClick={close}>
                  Cancel
                </Button>
                <Button level="danger" onClick={close}>
                  Delete
                </Button>
              </Modal.BottomButtons>
            </Modal>
          )}
          <Button onClick={open}>Open Confirm Modal</Button>
        </>
  },
};

export const FullscreenOverlay: Story = {
  name: 'Fullscreen Overlay',
  render: (args) => {
    <>
          {isOpen && (
            <Modal closeTitle="Close" onClose={close}>
              <Modal.SectionTitle color="brand">Entity type</Modal.SectionTitle>
              <Modal.Title>Title of Overlay</Modal.Title>
              Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum
              ultrices diam.
              <Content width={900} height={400}>
                CONTENT
              </Content>
              <Modal.BottomButtons>
                <Button level="tertiary" onClick={close}>
                  Cancel
                </Button>
                <Button level="primary" onClick={close}>
                  Confirm
                </Button>
              </Modal.BottomButtons>
            </Modal>
          )}
          <Button onClick={open}>Open Fullscreen Overlay</Button>
        </>
  },
};

export const SplitScreenOverlay: Story = {
  name: 'Split screen Overlay',
  render: (args) => {
    <>
          {isOpen && (
            <Modal closeTitle="Close" onClose={close} illustration={<AddingValueIllustration />}>
              <Modal.SectionTitle color="brand">Entity type</Modal.SectionTitle>
              <Modal.Title>Title of Overlay</Modal.Title>
              Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum
              ultrices diam.
              <Content width={600} height={300}>
                CONTENT
              </Content>
              <Modal.BottomButtons>
                <Button level="tertiary" onClick={close}>
                  Cancel
                </Button>
                <Button level="primary" onClick={close}>
                  Confirm
                </Button>
              </Modal.BottomButtons>
            </Modal>
          )}
          <Button onClick={open}>Open Split screen Overlay</Button>
        </>
  },
};

