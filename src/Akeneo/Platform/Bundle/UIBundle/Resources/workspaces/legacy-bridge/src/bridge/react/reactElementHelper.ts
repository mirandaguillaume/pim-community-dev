import {createRoot, Root} from 'react-dom/client';
import {flushSync} from 'react-dom';

const roots = new WeakMap<Element, Root>();

/**
 * Track whether a container had its root created while connected to the DOM.
 * React 18 delegates events to the createRoot container (not document).
 * If the root was created on a detached container, event listeners may not
 * dispatch correctly even after the container is attached.
 */
const connectedWhenCreated = new WeakMap<Element, boolean>();

const mountReactElementRef = (component: JSX.Element, container: Element) => {
  let root = roots.get(container);
  const isConnected = container.isConnected;

  // Force recreate the root if:
  // 1. The root was originally created on a detached container, OR
  // 2. The container was detached and re-attached since root creation
  //    (detected by the root existing but the container not having been
  //    connected when the root was created)
  if (root && connectedWhenCreated.get(container) === false && isConnected) {
    root.unmount();
    roots.delete(container);
    connectedWhenCreated.delete(container);
    root = undefined;
  }

  if (!root) {
    root = createRoot(container);
    roots.set(container, root);
    connectedWhenCreated.set(container, isConnected);
  }

  flushSync(() => {
    root.render(component);
  });

  return container;
};

const unmountReactElementRef = (container: Element) => {
  const root = roots.get(container);
  if (root) {
    root.unmount();
    roots.delete(container);
    connectedWhenCreated.delete(container);
  }
};

export {mountReactElementRef, unmountReactElementRef};
