import { Upload } from './Upload.js'
import { Attachment } from './Interface.js'
import { EditNodeFormatter } from './EditNode/EditNodeFormatter.js'

/**
 * Wraps the form textarea to allow for the insertion of non-html nodes
 * that act as placeholders and can be converted to HTML when it's time
 * to submit the form
 */
export class UploadWrapper {
  textarea: HTMLTextAreaElement
  Upload: Upload
  attachments: Array<Attachment> = [];
  options: any = {};
  uploadType: string

  /**
   * 
   * @param inputId     - string/HTMLInputElement     [file input]
   * @param textfieldId - string/HTMLTextAreaElement  [text input]
   * @param options     - allows some customizing, see Upload class
   *                      _registerOptions class for more
   */
  public constructor(inputId, textfieldId, options: any = {}) {
    try {
      if (typeof options.uploadType === 'undefined')
        throw 'Missing required uploadType option property'
      this.Upload = new Upload(inputId, textfieldId, options)
      this.Upload.parent = this
      this.textarea = this._registerTextarea(textfieldId)
    } catch (e) {
      console.error(e)
    }
  }

  public forceAttachments(attachments: Array<Attachment>) {
    this.attachments = attachments
  }

  public addAttachment(attachment: Attachment) {
    console.log('UploadWrapper addAttachment: ', attachment, this)
    this.attachments.push(attachment)
    this._addNewAttachmentTagToText(attachment.hash)
    this._scrollTextareaToBottom()
  }

  public init() { }

  private _registerTextarea(textfieldId) {
    if (typeof textfieldId === 'string') {
      return <HTMLTextAreaElement>document.getElementById(textfieldId)
    }
    return textfieldId
  }

  private _addNewAttachmentTagToText(hashstr: string) {
    this.textarea.value = this.textarea.value + '\r\n\r\n' + hashstr + '\r\n'
  }

  private _scrollTextareaToBottom() {
    this.textarea.scrollTop = this.textarea.scrollHeight
  }

  public convertToHTML() {
    return EditNodeFormatter.toReadonly(this.textarea.value, this.attachments)
  }

  public disable() {
    this.Upload.disableInput()
  }

  public enable() {
    this.Upload.enableInput()
  }
}
