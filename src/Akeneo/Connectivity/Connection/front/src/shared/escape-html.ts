const entities: {[char: string]: string} = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
};

/**
 * Escapes a value before it is interpolated into markup rendered with dangerouslySetInnerHTML.
 */
export const escapeHtml = (value: string): string => value.replace(/[&<>"']/g, char => entities[char]);
