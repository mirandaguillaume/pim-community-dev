import { createContext } from 'react';
var TableContext = createContext({
    isSelectable: false,
    hasWarningRows: false,
    hasLockedRows: false,
    displayCheckbox: false,
    isDragAndDroppable: false,
    onReorder: undefined,
});
export { TableContext };
//# sourceMappingURL=TableContext.js.map