import React, {useState} from 'react';
import {Content} from '../../storybook/PreviewGallery';
import {Button, Modal} from '../../components';
import {AddingValueIllustration, DeleteIllustration} from '../../illustrations';

export default {
  title: 'Patterns/Overlays',
};

export const ConfirmModal = {
  render: args => {
    const [isOpen, setOpen] = useState(false);
    const open = () => setOpen(true);
    const close = () => setOpen(false);

    return (
      <>
        {isOpen && (
          <Modal closeTitle="Close" onClose={close} illustration={<DeleteIllustration />}>
            <Modal.SectionTitle color="brand">Products</Modal.SectionTitle>
            <Modal.Title>Confirm deletion</Modal.Title>Are you sure you want to delete this product?
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
    );
  },

  name: 'Confirm Modal',
};

export const FullscreenOverlay = {
  render: args => {
    const [isOpen, setOpen] = useState(false);
    const open = () => setOpen(true);
    const close = () => setOpen(false);

    return (
      <>
        {isOpen && (
          <Modal closeTitle="Close" onClose={close}>
            <Modal.SectionTitle color="brand">Entity type</Modal.SectionTitle>
            <Modal.Title>Title of Overlay</Modal.Title>Suspendisse lectus tortor, dignissim sit amet, adipiscing nec,
            ultricies sed, dolor. Cras elementum ultrices diam.
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
    );
  },

  name: 'Fullscreen Overlay',
};

export const SplitScreenOverlay = {
  render: args => {
    const [isOpen, setOpen] = useState(false);
    const open = () => setOpen(true);
    const close = () => setOpen(false);

    return (
      <>
        {isOpen && (
          <Modal closeTitle="Close" onClose={close} illustration={<AddingValueIllustration />}>
            <Modal.SectionTitle color="brand">Entity type</Modal.SectionTitle>
            <Modal.Title>Title of Overlay</Modal.Title>Suspendisse lectus tortor, dignissim sit amet, adipiscing nec,
            ultricies sed, dolor. Cras elementum ultrices diam.
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
    );
  },

  name: 'Split screen Overlay',
};
