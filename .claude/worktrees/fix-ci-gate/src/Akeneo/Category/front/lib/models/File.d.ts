import { FileInfo } from 'akeneo-design-system';
export declare type FilePath = string;
export declare type File = FileInfo | null;
export declare const createEmptyFile: () => File;
export declare const createFileFromNormalized: (file: any) => File;
export declare const isFileEmpty: (file: File) => file is null;
export declare const areFilesEqual: (first: File, second: File) => boolean;
export declare const isFileInStorage: (file: File) => boolean;
export declare const isFile: (file: any) => file is File;
