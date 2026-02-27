export declare enum MediaPreviewType {
    Preview = "preview",
    Thumbnail = "thumbnail",
    ThumbnailSmall = "thumbnail_small"
}
export declare type MediaPreview = {
    type: MediaPreviewType;
    attributeCode: string;
    data: string;
};
