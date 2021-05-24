import { Logger } from '../../Logger/Logger.js'
import { ProgressBar } from './ProgressBar.js'
import { Action } from './Action.js'
import { TagNode, Attachment } from './Interface.js'
import { csl, get, isJSON } from '../../../Lib/Lib.js'
import { Response } from '../../Response/Response.js'
import { TagFactory } from './TagFactory.js'
import { Request } from '../../Fetch/Request.js'

export class Upload {
    parent: any
    progressBarContainerId: string = 'ui:fileUploadContainer';
    input: HTMLInputElement
    inputLabel: HTMLLabelElement
    text: HTMLTextAreaElement
    fileCache: Array<TagNode> = [];
    progressBar: ProgressBar
    progressFilename: HTMLElement
    cancelButton: HTMLButtonElement
    submit: HTMLButtonElement
    maxSize: number = 50; //MB
    xhrPointer: any
    uploadStart: number
    uploadDuration: number
    minUploadDuration: number = 1000; // ms
    pauseUploadCompletion: boolean = false;
    options: any = {};
    Ui: any
    filetypes: Array<string> = [];
    extensions: Array<string> = [];

    public constructor(inputId, textfieldId, options = {}) {
        try {
            console.log('options: ', options)
            this._registerOptions(options)
            this._loadMetaGlobals()
            this._registerInput(inputId)
            this._registerInputLabel()
            this._registerTextfield(textfieldId)
            this._registerProgressBar()
            this._init()
            console.log('UPLAOD DETAIL : ', this)
        } catch (e) {
            console.error('UPLOAD: ERROR: e:', e)
            Logger.error(JSON.stringify(e))
        }
    }

    private _loadMetaGlobals() {
        this._loadFileTypes()
        this._loadFileExtensions()
    }

    private _loadFileExtensions() {
        Action.getAllowedExtensions().then((res) => {
            csl("red", "here dummy")
            console.log('res:', res)
            const ft = <string>(<any>res.data)
            this.extensions = ft.split(',')
        })
    }
    private _loadFileTypes() {
        Action.getFileTypes().then((res) => {
            this.filetypes = <Array<string>>(<any>res.data)
        })
    }

    private _registerInputLabel() {
        this.inputLabel = <HTMLLabelElement>this.input.parentElement
    }

    private _registerProgressBar() {
        this.progressBar = new ProgressBar()
        this.progressBar.setContainer(this.progressBarContainerId)
        this.progressBar.Upload = this
    }

    private _registerOptions(options: any) {
        if (options.hasOwnProperty('progressBarContainerId')) {
            this['progressBarContainerId'] = options.progressBarContainerId
        }
        if (typeof options.submitId === 'string') {
            this.options['submitId'] = options.submitId
            this._registerSubmitButton()
        }
        if (typeof options.uploadType === 'string') {
            this.options.uploadType = options.uploadType
        }
    }

    private _registerSubmitButton() {
        this.submit = <HTMLButtonElement>get(this.options.submitId)
    }

    public getUploadedFiles() {
        return this.fileCache
    }

    private _registerInput(inputId) {
        if (typeof inputId === 'string') {
            this.input = <HTMLInputElement>get(inputId)
        } else {
            this.input = <HTMLInputElement>inputId
        }
    }

    private _registerTextfield(textfieldId) {
        if (typeof textfieldId === 'string') {
            this.text = <HTMLTextAreaElement>get(textfieldId)
        } else {
            this.text = <HTMLTextAreaElement>textfieldId
        }
    }

    private _init() {
        this._initListeners()
    }

    private _initListeners() {
        this.input.addEventListener('change', (e) => this._processUpload(e))
        window.addEventListener('keyup', (e) => {
            if (e.key == 'G' && e.shiftKey) {
                // just for inline debuggin
            }
        })
    }

    public abort() {
        if (this.xhrPointer) this.xhrPointer.abort()
        Logger.info('Upload aborted by user action')
    }

    public disableInput() {
        this.input.disabled = true
    }

    public enableInput() {
        this.input.disabled = false
    }

    private _disableForm() {
        if (this.text) this.text.disabled = true
        if (this.submit) this.submit.disabled = true
        this._disableInput()
    }

    private _enableForm() {
        if (this.text) this.text.disabled = false
        if (this.submit) this.submit.disabled = false
        this._enableInput()
    }

    private _disableInput() {
        this.input.disabled = true
        this.inputLabel.classList.add('disable-label')
    }

    private _enableInput() {
        this.input.disabled = false
        this.inputLabel.classList.remove('disable-label')
    }

    private _resetUploadProgressBar() {
        this.progressBar.reset()
    }

    private _processUpload(event) {
        const files = this._validateFiles(event.target.files)
        this._disableForm()
        this._uploadAllFiles(files)
    }

    /**
     * Checks each file for filesize against max prop. Files > max are
     * filtered and removed from the uploads and an alert is given to the
     * user
     *
     * @param files: FileList
     */
    private _validateFiles(files: FileList) {
        let validatedFiles = this._validateFileSize(files)
        validatedFiles = this._validateFileTypes(validatedFiles)
        return validatedFiles
    }

    private _validateFileTypes(files: Array<File>) {
        return Array.from(files).filter((file) => {
            if (this.filetypes.indexOf(file.type) === -1) {
                alert(
                    `Incorrect file type found: ${file.name} has type ${file.type}. File removed`,
                )
                return false
            }
            return true
        })
    }

    private _validateFileSize(files: FileList) {
        return Array.from(files).filter((file) => {
            const filesize = file.size / 1024 / 1024
            if (filesize > this.maxSize) {
                alert(
                    `File size too large: ${file.name} removed (${Math.round(filesize * 100) / 100
                    }MB). Max filesize value: ${this.maxSize}MB`,
                )
                throw 'File upload size exceeded error alerted'
            }
            return filesize < this.maxSize
        })
    }

    private _uploadAllFiles(files) {
        console.log('upload all files:', files)
        try {
            this._xhrUpload(files)
        } catch (e) {
            console.error(e)
        }
    }

    private _xhrUpload(files) {
        const filesArr = [...files]
        if (filesArr.length <= 0) {
            this._completeXHRUpload()
            return
        }
        const first = filesArr.shift()
        this.uploadStart = Date.now()
        const xhr = new XMLHttpRequest()
        xhr.withCredentials = true
        xhr.onreadystatechange = () => {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                if (this.pauseUploadCompletion) {
                    this._pauseResponse(xhr.responseText, filesArr)
                } else {
                    this._executeNextUpload(xhr.responseText, filesArr)
                }
            }
        }
        this._showContainer()
        this._setXHRListeners(xhr)
        xhr.open('POST', '/upload', true)
        xhr.setRequestHeader('X-CSRF-TOKEN', Request.getScrfToken())
        xhr.send(this._buildFormData(first))
        this.xhrPointer = xhr
    }

    private _executeNextUpload(responseText, files) {
        this._handleDoneReponse(responseText)
        this._xhrUpload(files)
    }

    private _pauseResponse(responseText, files) {
        this._delayDisplayFullProgressBar()
        const to = setTimeout(() => {
            this._executeNextUpload(responseText, files)
            this.pauseUploadCompletion = false
            clearTimeout(to)
        }, this.minUploadDuration)
    }

    private _delayDisplayFullProgressBar() {
        setTimeout(() => {
            this.progressBar.setBarWidth(100)
        }, this.minUploadDuration - 250)
    }

    private _completeXHRUpload() {
        this._hideContainer()
        this._clearInputValue()
        this._enableForm()
    }

    private _hideContainer() {
        this.progressBar.hide()
    }

    private _showContainer() {
        this.progressBar.show()
    }

    private _setXHRListeners(xhr) {
        xhr.upload.addEventListener('progress', (e) => this._updateProgress(e))
        xhr.upload.addEventListener('load', (e) => this._transferComplete(e))
        xhr.upload.addEventListener('error', (e) => this._transferFailed(e))
        xhr.upload.addEventListener('abort', (e) => this._transferAborted(e))
    }

    private _buildFormData(file) {
        const form = new FormData()
        form.append('uploadType', this.options.uploadType)
        form.append('file', file)
        this.progressBar.setFilename(file.name)
        return form
    }

    private _updateProgress(event) {
        let percentLoaded = (event.loaded / event.total) * 100
        const uploadDuration = Date.now() - this.uploadStart
        if (percentLoaded >= 100) {
            if (uploadDuration < this.minUploadDuration) {
                percentLoaded = 70
                this.pauseUploadCompletion = true
            }
        }
        this.progressBar.setBarWidth(percentLoaded)
    }

    private _handleDoneReponse(xhrRes) {
        console.log('xhrRes:', xhrRes)
        if (isJSON(xhrRes)) {
            const response = JSON.parse(xhrRes)
            this._handleXHRUpload(response)
            this._resetUploadProgressBar()
        }
    }

    private _transferComplete(event) {
        console.warn('file transfer complete')
    }

    private _transferFailed(event) {
        alert('File Upload failed. please try again')
        this._enableForm()
        this._enableInput()
    }

    private _transferAborted(event) {
        Response.put('info', 'File upload cancelled by user')
        this._enableForm()
        this._enableInput()
    }

    private _handleXHRUpload(response) {
        console.log('response:', response)
        try {
            const tagNode = <TagNode>TagFactory.create(response)
            console.log('tagNode:', tagNode)
            this.fileCache.push(tagNode)
            // this._createNewAnchorTag(tagNode.hash);
            // this._scrollTextAreaToBottom();
            if (!this.parent) return
            if (typeof this.parent.addAttachment !== 'function') return
            this.parent.addAttachment(<Attachment>{
                details: {},
                hash: tagNode.hash,
                html: tagNode.html,
            })
        } catch (e) {
            console.error(e)
            Response.put('danger', 'Error uploading file - Please try again later')
            alert('Error uploading file. Please retry again later')
            this.progressBar.reset()
            this._enableForm()
            this._enableInput()
        }
    }

    private _clearInputValue() {
        this.input.value = ''
    }

    public get(hash) {
        return this.fileCache.filter((node) => {
            return node.hash === hash.replace('\r\n', '')
        })
    }
}
