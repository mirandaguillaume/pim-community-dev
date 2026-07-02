import React, { useRef } from 'react';
import { Button, DeleteIllustration, Modal, useAutoFocus } from 'akeneo-design-system';
import { useTranslate } from '../hooks';
var DeleteModal = function (_a) {
    var children = _a.children, title = _a.title, confirmDeletionTitle = _a.confirmDeletionTitle, confirmButtonLabel = _a.confirmButtonLabel, cancelButtonLabel = _a.cancelButtonLabel, _b = _a.canConfirmDelete, canConfirmDelete = _b === void 0 ? true : _b, onConfirm = _a.onConfirm, onCancel = _a.onCancel, illustration = _a.illustration;
    var translate = useTranslate();
    var cancelRef = useRef(null);
    useAutoFocus(cancelRef);
    return (React.createElement(Modal, { closeTitle: translate('pim_common.close'), onClose: onCancel, illustration: illustration !== null && illustration !== void 0 ? illustration : React.createElement(DeleteIllustration, null) },
        React.createElement(Modal.SectionTitle, { color: "brand" }, title),
        React.createElement(Modal.Title, null, confirmDeletionTitle !== null && confirmDeletionTitle !== void 0 ? confirmDeletionTitle : translate('pim_common.confirm_deletion')),
        children,
        React.createElement(Modal.BottomButtons, null,
            React.createElement(Button, { level: "tertiary", onClick: onCancel, ref: cancelRef }, cancelButtonLabel !== null && cancelButtonLabel !== void 0 ? cancelButtonLabel : translate('pim_common.cancel')),
            React.createElement(Button, { level: "danger", disabled: !canConfirmDelete, onClick: onConfirm }, confirmButtonLabel !== null && confirmButtonLabel !== void 0 ? confirmButtonLabel : translate('pim_common.delete')))));
};
export { DeleteModal };
//# sourceMappingURL=DeleteModal.js.map