import {Deferred} from 'jquery';
import {mountReactElementRef, unmountReactElementRef} from './reactElementHelper';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
abstract class ReactController extends BaseController {
  /**
   * Base React element to mount (and keep as ref between route changes).
   */
  abstract reactElementToMount(): JSX.Element;

  /**
   * RegEx should match the base 'route' of the controller.
   * The goal in to avoid to mount/unmount React between route changes and keep the same React element ref while in the
   * controller/context.
   */
  abstract routeGuardToUnmount(): RegExp;

  /**
   * Return a static reference to a HTMLElement
   */
  getContainerRef(): Element {
    return this.$el.get(0);
  }

  initialize() {
    mediator.on('route_start', this.handleRouteChange, this);

    return super.initialize();
  }

  renderRoute() {
    const container = this.getContainerRef();

    // Attach the container to the DOM BEFORE creating the React 18 root.
    // React 18 delegates events to the createRoot container (not document like React 17).
    // Event listeners set up on a detached container may not dispatch correctly.
    if (container !== this.$el.get(0)) {
      this.$el.append(container);
    }

    mountReactElementRef(this.reactElementToMount(), container);

    return Deferred().resolve();
  }

  remove() {
    mediator.off('route_start', this.handleRouteChange, this);

    this.$el.remove();

    return super.remove();
  }

  /**
   * Avoid React mount/unmount between route changes.
   */
  private handleRouteChange(routeName: string) {
    if (true === this.routeGuardToUnmount().test(routeName)) {
      return;
    }

    unmountReactElementRef(this.getContainerRef());
  }
}

export {ReactController};
