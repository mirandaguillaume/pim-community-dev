"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.isJobFinished = exports.getDownloadLinks = void 0;
var getDownloadLinks = function (jobExecutionArchives) {
    if (!jobExecutionArchives) {
        return [];
    }
    var downloadLinks = [];
    Object.keys(jobExecutionArchives).forEach(function (archiver) {
        var archive = jobExecutionArchives[archiver];
        var fileNames = Object.keys(archive.files);
        if (fileNames.length === 1) {
            downloadLinks.push({
                label: archive.label,
                archiver: archiver,
                key: fileNames[0],
            });
            return;
        }
        fileNames.forEach(function (fileName) {
            downloadLinks.push({
                label: fileName,
                archiver: archiver,
                key: fileName,
            });
        });
    });
    return downloadLinks;
};
exports.getDownloadLinks = getDownloadLinks;
var isJobFinished = function (jobExecution) {
    return null !== jobExecution &&
        !['STARTING', 'IN_PROGRESS', 'STOPPING', 'PAUSING', 'PAUSED'].includes(jobExecution.tracking.status);
};
exports.isJobFinished = isJobFinished;
//# sourceMappingURL=JobExecution.js.map