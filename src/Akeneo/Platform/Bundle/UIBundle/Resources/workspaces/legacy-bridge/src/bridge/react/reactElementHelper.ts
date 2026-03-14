import {createRoot, Root} from 'react-dom/client';

const roots = new WeakMap<Element, Root>();

const mountReactElementRef = (component: JSX.Element, container: Element) => {
  let root = roots.get(container);
  if (!root) {
    root = createRoot(container);
    roots.set(container, root);
  }
  root.render(component);

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
