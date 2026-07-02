var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
import React, { useState } from 'react';
import { Editor } from 'react-draft-wysiwyg';
import draftToHtml from 'draftjs-to-html';
import htmlToDraft from 'html-to-draftjs';
import { ContentState, convertToRaw, EditorState } from 'draft-js';
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';
var editorStateToRaw = function (editorState) {
    return draftToHtml(convertToRaw(editorState.getCurrentContent()));
};
var rawToEditorState = function (value) {
    var rawDraft = htmlToDraft(value);
    if (!rawDraft || !rawDraft.contentBlocks) {
        return EditorState.createEmpty();
    }
    return EditorState.createWithContent(ContentState.createFromBlockArray(rawDraft.contentBlocks));
};
var RichTextEditor = function (_a) {
    var value = _a.value, _b = _a.readOnly, readOnly = _b === void 0 ? false : _b, onChange = _a.onChange, rest = __rest(_a, ["value", "readOnly", "onChange"]);
    var _c = useState(rawToEditorState(value)), editorState = _c[0], setEditorState = _c[1];
    var handleChange = function (editorState) {
        setEditorState(editorState);
        onChange(editorStateToRaw(editorState));
    };
    return (React.createElement(Editor, __assign({ toolbarHidden: readOnly, readOnly: readOnly, toolbar: {
            options: ['inline', 'blockType', 'fontSize', 'fontFamily', 'list', 'link', 'embedded', 'image', 'remove'],
            inline: {
                options: ['bold', 'italic'],
            },
        }, onEditorStateChange: handleChange }, rest, { editorState: editorState })));
};
export { RichTextEditor };
//# sourceMappingURL=RichTextEditor.js.map