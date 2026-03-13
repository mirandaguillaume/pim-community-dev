// Type declarations for non-code imports (replaces react-scripts types)
declare module '*.svg' {
  import * as React from 'react';
  export const ReactComponent: React.FunctionComponent<React.SVGProps<SVGSVGElement>>;
  const src: string;
  export default src;
}

declare module '*.css' {
  const classes: {readonly [key: string]: string};
  export default classes;
}
