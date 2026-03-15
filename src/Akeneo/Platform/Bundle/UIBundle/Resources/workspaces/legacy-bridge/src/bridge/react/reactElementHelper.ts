import {createRoot, Root} from 'react-dom/client';
import {flushSync} from 'react-dom';

const roots = new WeakMap<Element, Root>();

const mountReactElementRef = (component: JSX.Element, container: Element) => {
  let root = roots.get(container);

  // Always tear down and recreate the React 18 root.
  // React 18 attaches event-delegation listeners to the createRoot container
  // instead of document (React 17 behaviour). Recreating the root guarantees
  // a clean event-delegation setup on every renderRoute() call.
  if (root) {
    root.unmount();
    roots.delete(container);
    root = undefined;
  }

  root = createRoot(container);
  roots.set(container, root);

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
