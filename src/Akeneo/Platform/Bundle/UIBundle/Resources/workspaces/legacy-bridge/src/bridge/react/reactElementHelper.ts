import {createRoot, Root} from 'react-dom/client';
import {flushSync} from 'react-dom';

const roots = new WeakMap<Element, Root>();

const mountReactElementRef = (component: JSX.Element, container: Element) => {
  let root = roots.get(container);
  if (!root) {
    root = createRoot(container);
    roots.set(container, root);
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
  }
};

export {mountReactElementRef, unmountReactElementRef};
