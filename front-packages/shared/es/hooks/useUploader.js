import { useBooleanState } from 'akeneo-design-system';
import { useRouter } from './useRouter';
import { useCallback } from 'react';
var useUploader = function (uploadRoute) {
    var router = useRouter();
    var _a = useBooleanState(), isUploading = _a[0], startUploading = _a[1], stopUploading = _a[2];
    var uploader = useCallback(function (file, onProgress) {
        return new Promise(function (resolve, reject) {
            var formData = new FormData();
            formData.append('file', file);
            startUploading();
            var xhr = new XMLHttpRequest();
            xhr.open('POST', router.generate(uploadRoute), true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.addEventListener('progress', function (event) { return onProgress(event.loaded / event.total); }, false);
            xhr.addEventListener('load', function () {
                stopUploading();
                if (xhr.status === 200) {
                    resolve(JSON.parse(xhr.response));
                }
                else {
                    reject(xhr.response || []);
                }
            });
            xhr.send(formData);
        });
    }, [router, uploadRoute]);
    return [uploader, isUploading];
};
export { useUploader };
//# sourceMappingURL=useUploader.js.map