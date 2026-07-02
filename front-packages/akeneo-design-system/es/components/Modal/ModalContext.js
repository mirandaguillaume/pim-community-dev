import React, { useContext } from 'react';
var ModalContext = React.createContext(false);
var useInModal = function () { return useContext(ModalContext); };
export { useInModal, ModalContext };
//# sourceMappingURL=ModalContext.js.map