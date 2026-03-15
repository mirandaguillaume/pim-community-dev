import {createRoot, Root} from 'react-dom/client';
import {flushSync} from 'react-dom';

let container: Element | null = null;
let root: Root | null = null;

export const getOrCreateContainer = (): Element => {
  if (null === container) {
    container = document.createElement('div');
  }

  return container;
};

export const mountReactElementRef = (component: JSX.Element) => {
  if (null === container) {
    container = document.createElement('div');
  }

  if (null === root) {
    root = createRoot(container);
  }

  flushSync(() => {
    root!.render(component);
  });

  return container;
};

export const unmoundReactElementRef = () => {
  if (null !== root) {
    root.unmount();
    root = null;
    container = null;
  }
};
