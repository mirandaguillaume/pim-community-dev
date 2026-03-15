import ReactDOM from 'react-dom';

// Use the legacy ReactDOM.render() API even under React 18.
//
// ReactController subclasses (CategoriesSettings, identifier-generator,
// process-tracker, measurements) are Backbone controllers that manage their
// own DOM lifecycle.  React 18's createRoot() attaches event-delegation
// listeners to the container element instead of `document`.  In this
// Backbone→React bridge context, native browser events dispatched by
// Selenium/ChromeDriver don't always bubble to the container in time,
// causing onClick handlers on <tr> and similar elements to silently fail.
//
// The legacy API delegates events to `document` (same as React 17),
// which is the correct behaviour for these bridge components where
// Backbone controls the DOM hierarchy.

const mountReactElementRef = (component: JSX.Element, container: Element) => {
  ReactDOM.render(component, container);

  return container;
};

const unmountReactElementRef = (container: Element) => {
  ReactDOM.unmountComponentAtNode(container);
};

export {mountReactElementRef, unmountReactElementRef};
