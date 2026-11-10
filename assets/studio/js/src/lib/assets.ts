/** Assets that can be rotated by AssetController::rotateLeftAction: images and PDFs. */
export const isRotatable = (type?: string, mimeType?: string | null): boolean => type === 'image' || mimeType === 'application/pdf'
