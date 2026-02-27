import { Router } from '@akeneo-pim-community/shared';
import { File } from '../models/File';
import { MediaPreview } from '../models/MediaPreview';
declare const canCopyToClipboard: () => boolean;
declare const copyToClipboard: (text: string) => false | Promise<void>;
declare const getImageDownloadUrl: (router: Router, image: File) => string;
declare const getMediaPreviewUrl: (router: Router, mediaPreview: MediaPreview) => string;
export { canCopyToClipboard, copyToClipboard, getImageDownloadUrl, getMediaPreviewUrl };
