// TEMPORARY D5 diagnostic — painted into a fixed body overlay so the Behat failure SCREENSHOT captures
// the filters-column/filters-selector ↔ picker-modal ↔ save sequence. REVERT before merge.
const LINES: string[] = [];
let el: HTMLElement | null = null;

export const d5Log = (msg: string): void => {
  LINES.push(msg);
  if (LINES.length > 30) {
    LINES.shift();
  }

  if (null === el) {
    el = document.createElement('div');
    el.id = 'd5-debug';
    el.setAttribute(
      'style',
      'position:fixed;top:0;left:0;z-index:99999;background:rgba(0,0,0,.88);color:#0f0;' +
        'font:10px/1.3 monospace;padding:6px;max-width:620px;white-space:pre-wrap;pointer-events:none;'
    );
    document.body.appendChild(el);
  }

  el.textContent = LINES.join('\n');
};
