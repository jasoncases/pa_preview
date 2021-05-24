export interface Attachment {
  hash: string;
  html: string;
  details: AttachmentDetails;
}

export interface AttachmentDetails {
  src: string;
  alt: string;
  width?: number;
}

export interface TagNode {
  hash: string;
  html: string;
}
