"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.isFile = exports.isFileInStorage = exports.areFilesEqual = exports.isFileEmpty = exports.createFileFromNormalized = exports.createEmptyFile = void 0;
var createEmptyFile = function () { return null; };
exports.createEmptyFile = createEmptyFile;
var createFileFromNormalized = function (file) { return file; };
exports.createFileFromNormalized = createFileFromNormalized;
var isFileEmpty = function (file) { return null === file; };
exports.isFileEmpty = isFileEmpty;
var areFilesEqual = function (first, second) {
    return (null === first && null === second) ||
        (null !== first &&
            null !== second &&
            first.filePath === second.filePath &&
            first.originalFilename === second.originalFilename &&
            first.size === second.size &&
            first.mimeType === second.mimeType &&
            first.extension === second.extension);
};
exports.areFilesEqual = areFilesEqual;
var isFileInStorage = function (file) { return !(0, exports.isFileEmpty)(file) && -1 === file.filePath.indexOf('/tmp/'); };
exports.isFileInStorage = isFileInStorage;
var isFile = function (file) {
    return null === file ||
        (typeof file === 'object' && typeof file.originalFilename === 'string' && typeof file.filePath === 'string');
};
exports.isFile = isFile;
//# sourceMappingURL=File.js.map