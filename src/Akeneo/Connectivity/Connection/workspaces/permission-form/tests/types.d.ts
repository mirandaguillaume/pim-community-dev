interface JQuery {
  select2(): JQuery;
}

// Legacy PIM modules resolved by the rspack alias map (requirejs.yml). They
// were consumed through untyped `require()` calls until the ESM migration;
// the ES imports now need ambient declarations for `tsc --noEmit --strict`.
declare module 'oro/translator' {
  const translate: (key: string, placeholders?: Record<string, string | number>, count?: number) => string;
  export default translate;
}

declare module 'require-context' {
  const requireContext: (module: string) => any;
  export default requireContext;
}
