import { Template } from '../Components/Template/Template.js'
import { Creator } from '../Components/Creator/Creator.js'
import { _Window } from '../Components/_Window/_Window.js'
import { Throw } from '../../Throwable/Throw.js'
import { Messages } from './Message.js'

/**
 *
 * @param derivedCtor
 * @param baseCtors
 *
 * @returns void
 */
export function applyMixins(derivedCtor: any, baseCtors: any[]) {
    baseCtors.forEach((baseCtor) => {
        Object.getOwnPropertyNames(baseCtor.prototype).forEach((name) => {
            Object.defineProperty(
                derivedCtor.prototype,
                name,
                Object.getOwnPropertyDescriptor(baseCtor.prototype, name)
            )
        })
    })
}

/**************************************************************************
 * Library Interfaces
 **************************************************************************/

/**
 * Flexible Runtime Configuration Object
 */
export interface RuntimeConfigurationObject {
    [key: string]: any
}

export class CreatorIncludeInterface {
    Creator: Creator
    _registerCreator(Creator: Creator) {
        this.Creator = Creator
    }
}

export class TemplateIncludeInterface {
    Template: Template
    _registerTemplate(Template: Template) {
        this.Template = Template
    }
}

// Disposable Mixin
export class Disposable {
    isDisposed: boolean
    dispose() {
        this.isDisposed = true
    }
}

// Activatable Mixin
export class Activatable {
    isActive: boolean
    activate() {
        this.isActive = true
    }
    deactivate() {
        this.isActive = false
    }
}

// Deep object clone
export const deepCopyFunction = (inObject) => {
    let outObject, value, key

    if (typeof inObject !== 'object' || inObject === null) {
        return inObject // Return the value if inObject is not an object
    }

    // Create an array or object to hold the values
    outObject = Array.isArray(inObject) ? [] : {}

    for (key in inObject) {
        value = inObject[key]

        // Recursively (deep) copy for nested objects, including arrays
        outObject[key] =
            typeof value === 'object' && value !== null
                ? deepCopyFunction(value)
                : value
    }

    return outObject
}

export const returnNearestValueInArray = (array, value) => {
    return array.reduce(function (prev, curr) {
        return Math.abs(curr - value) < Math.abs(prev - value) ? curr : prev
    })
}

export function capitalize(string: string) {
    return string.replace(/^\w/, (c) => c.toUpperCase())
}

export function extendedSort(
    a: any,
    b: any,
    value: string,
    order: string = 'ASC'
) {
    //
    try {
        // break sorting by type string/number
        if (typeof a[value] === 'string') {
            if (a[value] === null || a[value] === undefined) {
                throw `${value} is invalid in ${JSON.stringify(
                    a
                )}. This value MUST be set to be sorted.`
            }

            if (b[value] === null || b[value] === undefined) {
                throw `${value} is invalid in ${JSON.stringify(
                    b
                )}. This value MUST be set to be sorted.`
            }
            // all returns are swapped dependent on order value

            if (a[value].toLowerCase() === b[value].toLowerCase()) return 0 // values are equal
            if (a[value].toLowerCase() < b[value].toLowerCase()) {
                // a[value] is less
                return order === 'DESC' ? 1 : -1
            }
            return order === 'DESC' ? -1 : 1 // b[value] is less
        } else {
            return order === 'DESC' ? b[value] - a[value] : a[value] - b[value]
        }
    } catch (err) {
        console.error(`Caught Error: ${err}`)
    }
}

export function missingRequiredProps(propArr: Array<string>, obj: any) {
    return propArr.some((key) => {
        return obj[key] === undefined || obj[key] === null
    })
}

export function isJSON(string: string) {
    try {
        if (JSON.parse(string)) {
            return true
        }
    } catch (e) {
        return false
    }
}

export function applyCSSClasses(
    el: HTMLElement,
    cl: Array<string>,
    timer: number = 0
) {
    cl.forEach((c) => {
        el.classList.add(c)
    })
    if (timer === 0) return
    setTimeout(() => {
        removeCSSClasses(el, cl)
    }, timer)
}

export function removeCSSClasses(el: HTMLElement, cl: Array<string>) {
    cl.forEach((c) => {
        el.classList.remove(c)
    })
}

export interface FetchResponseInterface {
    status: string
    data: RuntimeConfigurationObject
}
/**
 * Returns {status: 'error' || ''success',
 *          data: JSON || text}
 * @param request The request object
 * @param data
 */
export function handleFetchResponse(
    request, // request object
    data, // initial data response from server
    expect: any = 'JSON', // format we want in return
    open = false // true = public route
): FetchResponseInterface {
    if (typeof expect == 'boolean') {
        open = expect
        expect = 'JSON'
    }
    if (!validateFetchResponse(request, data, expect, open)) return
    if (request.status === 200) {
        return handlFetchJSONResponse(data, expect)
    } else {
        return returnFetchResponse('error', data)
    }
}

function validateFetchResponse(request, data, expect, open) {
    if (!open) {
        return validateLoggedIn(data)
    }
    return true
}

function validateLoggedIn(data) {
    if (!isJSON(data)) return true // bypass if not JSON format
    const capturedData = JSON.parse(data)
    if (capturedData.hasOwnProperty('loggedIn')) {
        if (!capturedData.loggedIn) {
            if (capturedData.action === 'redirect') {
                window.location.href = '/landing'
                return false
            }
            return false
        }
    }
    return true
}
export function returnFetchResponse(status, data) {
    if (isJSON(data)) data = JSON.parse(data)
    return { status: status, data: data }
}

export function consoleErrorFetchResponse(data, msg = null) {
    msg = msg || 'Expected JSON. Response received: '
    console.error(msg, data)
}

export function handlFetchJSONResponse(data, expect) {
    if (expect === 'JSON') {
        if (isJSON(data)) {
            return returnFetchResponse('success', JSON.parse(data))
        } else {
            consoleErrorFetchResponse(data)
            return null
        }
    } else {
        return returnFetchResponse('success', data)
    }
}

export function padLeft(val: any, minLength: number = 2, str: string = '0') {
    val = String(val)
    if (val.length >= minLength) {
        return val
    } else {
        const fill = new Array(minLength - val.length)
        fill.fill(str, 0, minLength - val.length).push(val)
        return fill.join('')
    }
}

export function _asMoney(val: any) {
    let head, tail
    if (typeof val === 'undefined') return '0.00'
    if (isNaN(val)) val = '0.00'
    if (val === null) val = '0.00'
    if (typeof val === 'number') {
        val = round(val, 2).toString()
    }

    if (val.indexOf('.') >= 0) {
        [head, tail] = val.split('.')
        if (isNaN(head)) {
            console.log('In true: ', { head, tail })
        }
    } else {
        [head, tail] = [val, '']
        if (isNaN(head)) {
            console.log('In false: ', { head, tail })
        }
    }

    while (tail.length < 2) {
        tail += '0'
    }
    return `${head}.${tail}`
}

export function getUrlVars() {
    var vars = {}
    var parts = window.location.href.replace(
        /[?&]+([^=&]+)=([^&]*)/gi,
        function (m, key, value) {
            vars[key] = value
            return value
        }
    )
    return vars
}

export function clamp(value: number, min: number, max: number) {
    return value <= min ? min : value >= max ? max : value
}

export function minClamp(value: number, min: number) {
    return value <= min ? min : value
}

export function maxClamp(value: number, max: number) {
    return value >= max ? max : value
}

/**
 * Return Browser client
 *
 * @returns string - returns a *lowercase* string of the client browser
 */
export function detectClient() {
    const reArr = ['Safari', 'Firefox', 'Chrome']
    const _default = 'Chrome'
    const userAgent = window.navigator.userAgent
    let agent
    reArr.forEach((re) => {
        const match = userAgent.match(new RegExp(<string>re))
        if (match) {
            agent = re
        }
    })
    return agent ? agent.toLowerCase() : _default.toLowerCase()
}

export function lockOutputContainer() {
    const oC = document.getElementById('output-container')
    oC.setAttribute('style', `overflow: hidden; overflow-y: none`)
}

export function setOutputContainerToMobile() {
    const oC = document.getElementById('output-container')
    oC.setAttribute('style', `position: static`)
}

export function arraysAreEqual(arr1: Array<any>, arr2: Array<any>) {
    if (arr1.length != arr2.length) return
    return !arr1.some(el => arr2.indexOf(el) === -1)
}

export function get(id: string, mode: boolean = true) {
    // check for strict mode
    if (!mode) {
        let idString // declare var
        // if the first char in id var is a . or a #, pass the id along as the idString
        if (id.charAt(0) === '.' || id.charAt(0) === '#') {
            idString = id
        } else {
            // otherwise, an id fragment is assumed and it will grab all that start with that id
            idString = `[id^="${id}"]`
        }
        // return query select all, forcing a node list.
        return document.querySelectorAll(idString)
    } else {
        // return single element if strict mode
        return document.getElementById(id)
    }
}

export function isHTMLElement(el: any) {
    return typeof el.tagName === 'string'
}

export function _get(id: string, strict: any = true, el?: HTMLElement) {
    if (typeof strict === 'object') {
        el = strict
        strict = true
    }
    return gnuGet(id, strict, el)
}

export function gnuGet(id: string, strict: any = true, el?: HTMLElement) {
    if (typeof strict === 'object') {
        el = strict
        strict = true
    }
    if (strict) return strictGet(id, el)
    return nodelistGet(id, el)
}

export function nodelistGet(id: string, el?: HTMLElement) {
    if (el) return el.querySelectorAll(srcMapGet(id.charAt(0), id.charAt(1), id))
    return document.querySelectorAll(srcMapGet(id.charAt(0), id.charAt(1), id))
}

export function testGet(id: string) {
    return strictGet(id)
}

function strictGet(id: string, el?: HTMLElement) {
    if (el) return el.querySelector(srcMapGet(id.charAt(0), id.charAt(1), id))
    return document.querySelector(srcMapGet(id.charAt(0), id.charAt(1), id))
}

function srcMapGet(char1, char2, identifer) {
    let injectPrefixOperator = ''
    const arr = ['#', '!', '.', '^']
    if (arr.indexOf(char1) === -1) return identifer
    if (char1 === '.') return identifer
    if (char1 === '^' && char2.length > 1) Throw.err('Invalid identifier string provided')
    if (char1 === '^' && char2 === '^') Throw.err('Too many prefix operators')
    if (char1 === '^' || char2 === '^') return srcMapPrefix(char1, char2, identifer)
    return srcMap(char1, identifer)
}

function srcMap(op, id) {
    const src = {
        "!": `[data-id="${id.replace('!', "")}"]`,
        "#": `[id="${id.replace('#', "")}"]`,
    }
    return src[op]
}

function srcMapPrefix(char1, char2, identifer) {
    let c1
    let c2 = '^'
    if (char1 === '^') c1 = char2
    if (char2 === '^') c1 = char1
    return srcMap(c1, identifer.replace("^", "")).replace('=', '^=')
}


export function actionLog(msg: string, obj: any) {
    console.warn(`---`)
    console.warn(`--- Activity Log ----- ${obj.constructor.name}`)
    console.warn(`---`)
    console.warn(`--- Message: ${msg}`)
    console.warn(`---`)
}

export function round(val: number, spaces: number = 0) {
    const float: number = Boolean(spaces) ? Math.pow(10, spaces) : 1
    return Math.round(val * float) / float
}

/**
 * Return event.path of any given event. Chrome has it as a default,
 * Safari does not, so it's generated when not found natively
 *
 * @param event
 */
export function eventPath(event: any) {
    if (typeof event.path !== 'undefined') return event.path
    const path = []
    let curr = event.target
    while (curr.parentElement) {
        path.push(curr.parentElement)
        curr = curr.parentElement
    }
    return path
}

/**
 * Returns [BOOLEAN] if ancestorToCompare element is in the event.path
 * of the given event. Event.path is not currently present in Safari,
 * so it's generated via a while loop if event.path is undefined
 *
 * @param event
 * @param ancestorToCompare
 */
export function isAncestor(event: any, ancestorToCompare: HTMLElement) {
    return eventPath(event).indexOf(ancestorToCompare) >= 0
}

export function htmlentities(str: string) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
}

export function htmlentitiesDecode(str: string) {
    return String(str)
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
}

export function hashFnv32a(
    str: string = null,
    asString?: boolean,
    seed?: undefined
) {
    var i,
        l,
        hval = seed === undefined ? 0x811c9dc5 : seed

    const val = str || Math.round(Math.random() * Date.now()).toString()

    for (i = 0, l = val.length; i < l; i++) {
        hval ^= val.charCodeAt(i)
        hval += (hval << 1) + (hval << 4) + (hval << 7) + (hval << 8) + (hval << 24)
    }

    if (asString) {
        // Convert to 8 digit hex string
        return ('0000000' + (hval >>> 0).toString(16)).substr(-8)
    }

    return hval >>> 0
}

/**
 * Returns a boolean if the current window.innerWidth value is less than
 * the provided attr
 *
 * @param {number} [widthRed=750]
 *
 * @returns boolean
 */
export function isMobile(widthRes: number = 750) {
    return window.innerWidth < widthRes
}

export function hash64(str?: any, asString?: boolean, seed?: undefined) {
    if (typeof str === 'boolean') {
        asString = str
        str = null
    }
    var hash = hashFnv32a(str, asString, seed)
    return hash.toString() + hashFnv32a(hash + str, asString, seed)
}

export function closest(el: any, str: string, includeSiblings?: boolean) {
    const parent = <HTMLElement>el.parentElement
    console.log('parent:', parent)
    if (!parent) return
    if (parent.classList.contains(str) || parent.id === str || parent.tagName === str.toUpperCase()) {
        return parent
    }
    if (includeSiblings) {
        const sib = checkSiblings(parent, str)
        if (sib) return sib
    }
    return closest(parent, str, includeSiblings)
}

function checkSiblings(el: HTMLElement, str: string) {
    const parent = el.parentElement
    if (!parent) return false
    const sibs = Array.from(parent.children)
    console.log('sibs:', sibs)
    if (sibs.length <= 0) return false
    const found = sibs.filter(sib => {
        if (sib.classList.contains(str) || sib.id === str || sib.tagName === str.toUpperCase()) {
            console.log('ITS THIS ONE: ', sib)
            return sib
        }
    })[0]
    return found
}

export function outsideClick(classObj: any) {
    _Window.getInstance().addOutsideClickElement(classObj)
}

export function longpressClick(classObj: any) {
    _Window.getInstance().addLongpressClick(classObj)
}

export function doubleClick(classObj: any) {
    _Window.getInstance().addDoubleClick(classObj)
}

export function HEXtoHSL(hex: string) {
    hex = hex.replace(/#/g, '')
    if (hex.length === 3) {
        hex = hex
            .split('')
            .map(function (hex) {
                return hex + hex
            })
            .join('')
    }
    var result = /^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})[\da-z]{0,0}$/i.exec(hex)
    if (!result) {
        return null
    }
    var r = parseInt(result[1], 16)
    var g = parseInt(result[2], 16)
    var b = parseInt(result[3], 16)
        ; (r /= 255), (g /= 255), (b /= 255)
    var max = Math.max(r, g, b),
        min = Math.min(r, g, b)
    var h,
        s,
        l = (max + min) / 2
    if (max == min) {
        h = s = 0
    } else {
        var d = max - min
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
        switch (max) {
            case r:
                h = (g - b) / d + (g < b ? 6 : 0)
                break
            case g:
                h = (b - r) / d + 2
                break
            case b:
                h = (r - g) / d + 4
                break
        }
        h /= 6
    }
    s = s * 100
    s = Math.round(s)
    l = l * 100
    l = Math.round(l)
    h = Math.round(360 * h)

    return {
        h: h,
        s: s,
        l: l,
    }
}

export function textColorByHex(hex: string, threshold: number = 50) {
    const white = '#FFFFFF'
    const black = '#000000'
    return HEXtoHSL(hex).l < threshold ? white : black
}

export function hexToRGB(hex: string) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
    return result
        ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16),
        }
        : null
}

export function relativeLuminence(r: number, g: number, b: number) {
    const rsR = r / 255
    const rsG = g / 255
    const rsB = b / 255

    const fR = sRGBSpace(rsR)
    const fG = sRGBSpace(rsG)
    const fB = sRGBSpace(rsB)

    return 0.2126 * fR + 0.7152 * fG + 0.0722 * fB
}

export function sRGBSpace(v) {
    return v > 0.03928 ? Math.pow((v + 0.055) / 1.055, 2.4) : v / 12.92
}

/**
 * * refrence link: https://webaim.org/articles/contrast/
 *
 * Displays {color1} when the resultant relative luminence is higher
 * than the contrast ratio. Accessibility metrics look for 4.5:1
 * contrast ratio for body text and a 3:1 for larger headline text
 *
 * @param colorHex
 * @param color1    -
 * @param color2
 */
export function textByLuminence(
    colorHex: string,
    color1: string = '#000000',
    color2: string = '#FFFFFF'
) {
    const contrastRatio = 0.2222
    const color = hexToRGB(colorHex)
    const relLum = relativeLuminence(color.r, color.g, color.b)
    return relLum > contrastRatio ? color1 : color2
}

export function showModalBlocker() {
    const modalBlocker = <HTMLElement>gnuGet('!modal-blocker')
    if (modalBlocker) modalBlocker.style.display = 'block'
}

export function hideModalBlocker() {
    console.warn('hide modal blocker called....')
    const modalBlocker = <HTMLElement>gnuGet('!modal-blocker')
    if (modalBlocker) modalBlocker.style.display = 'none'
}

export function disableButton(btn: HTMLButtonElement, loading: boolean = false) {
    const w = btn.getBoundingClientRect().width
    btn.style.width = `${w}px`
    btn.disabled = true
    if (loading)
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'
}

export function enableButton(btn: HTMLButtonElement, restoreText: string = null) {
    btn.removeAttribute('disabled')
    if (restoreText)
        btn.innerHTML = restoreText
}


export function convert12To24Hr(ampmString: string) {
    const d = new Date(`01/01/2000 ${validateAMPMString(ampmString)}`)
    return `${padLeft(d.getHours(), 2, '0')}:${padLeft(d.getMinutes(), 2, '0')}`
}

function validateAMPMString(string) {
    const str = string.split('').filter(x => x != ' ')
    const ampm = str.map(x => isNaN(x) ? x.toLowerCase() : null).filter(x => x)
    const time = str.filter(x => !x.match(/[a-zA-Z]/))
    const ampmOout = ampm.indexOf('a') >= 0 ? 'AM' : 'PM'
    return `${time.join('')} ${ampmOout} `
}

export function convert24to12Hr(miltime: string) {
    const [hr, min, sec] = <Array<number>><any>miltime.split(':')
    const ampm = hr >= 12 ? 'PM' : 'AM'
    const hrs = hr > 12 ? hr - 12 : hr
    return `${hrs}:${min}${sec ? `:${sec}` : ''} ${ampm}`
}

/**
 * Returns a hash value for a generic js object. The specific use-case
 * was we had to compare objects from two, or more, sources that could
 * have similar names, emails, and/or overlapping id numbers. So we
 * decided to turn the object values into a string and hash the string.
 * Identical records would return the same hash, but the chances of a
 * collision decrease with more fields being selected
 *
 * @param record      - Generic JS object of key-value pairs
 * @param ignoreList  -
 */
export function identityHash(record: any, ignoreList: Array<string> = []) {
    // pull the keys and sort, so all keys will be in the proper order
    const keys = Object.keys(record).sort()
    const c = []
    keys.forEach(key => {
        // if the key is in the ignorelist, skip it
        if (ignoreList.indexOf(key) < 0) {
            // only key numbers and strings, so ignore arrays, objects, bools
            // etc
            if (typeof record[key] === 'string' || typeof record[key] === 'number')
                c.push(record[key])
        }
    })
    // return the has of the string
    return hash64(c.join(''), true)
}


export function csl(color: string, message: string) {
    console.log(`%c ${message}`, `background-color:${color};color:${textColorByHex(colorToHex(color))};padding:3px;font-size:14px;`)
}

function colorToHex(color: string) {
    switch (color) {
        case "red":
            return '#FF0000'
        case "blue":
            return '#0000FF'
        case "green":
            return '#00FF00'
        case "purple":
            return '#800080'
        case "yellow":
            return '#FFFF00'
        case "coral":
            return '#FF7F50'
    }
}

export function msg(module, key) {
    return Messages.message(module, key)
}

export function taskMsg(key) {
    return msg('task', key)
}

export function ticketMsg(key) {
    return msg('ticket', key)
}

export function codeMsg(key) {
    return msg('code', key)
}

