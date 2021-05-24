import { Fetch } from '../../../../System/Components/Fetch/Fetch.js'
import { FetchResponseInterface, hash64 } from '../../../../System/Lib/Lib.js'

/**
 * This class is a total mess. It was one of the first things that I
 * made for Proaction. I didn't have a clear idea of what Jason really
 * wanted and had to tweak and mess with things on the fly. I was also
 * working without a well-defined idea of what the end goal would be for
 * the framework, Proaction in general, and the Timesheets in general. I
 * managed to get this to work and then never came back to it because we
 * had very few problems with it. I don't think I will have time to give
 * this class the time it needs for a refactor, so feel free to rebuild
 * it. I will try to give it some comments so that it makes some sense
 * before I leave.
 *
 * I've had to make some changes in the porting to Laravel. My hope is
 * that some of these changes will make it easier to grok what this
 * Frankenstein's monster of a class is doing.
 */
export class Timeline {
    empId: number
    shift: any
    currentStatus: any
    id: string
    dotw: string
    min: number
    max: number
    shift_id: number
    offset: number
    hourRange: number
    gridUnits: number
    containerStart: number
    schedule: any
    ppHr: number
    indicator: any
    shiftContainer: HTMLElement
    barContainer: HTMLElement
    barWidth: number
    barLeft: number
    barStart: number

    constructor(shiftObj: TimelineData, empId) {
        this._setProps(shiftObj)
        this.empId = empId
        this.id = hash64(true)
        this._init()
    }

    private _init() {
        this.appendShiftContainer()
        this.drawGrid()
        this._drawShifts()
        this.appendDataRow()
        this._drawSchedule()
        this._appendCascade()
        this._appendIndicatorArrow()
        this._checkForShiftBarLeakage()
    }

    /**
     * Set initial class props from the provided TimelineData
     *
     * @param shiftObj
     */
    private _setProps(shiftObj: TimelineData) {
        this.shift = shiftObj
        this.currentStatus = shiftObj.current_status
        this.dotw = shiftObj.dotw
        this.min = shiftObj.min
        this.max = shiftObj.max
        this.hourRange = this.max - this.min
        this.gridUnits = this.hourRange / 0.5
        this.containerStart = this.min
        this.schedule = shiftObj.schedule ?? null
    }

    /**
     * Draw the schedule bar, if a schedule exists
     */
    private _drawSchedule() {
        if (typeof this.schedule.start !== 'undefined') {

            if (this.schedule.start > 0) {
                const target = document.getElementById('schedule-container-' + this.id)
                this.createBar(
                    this.schedule.start,
                    this.schedule.length,
                    'schedule-bar',
                    '#FFFFFF',
                    target,
                )
            }
        }
    }

    /**
     * Draw any shifts that are provided in the TimelineData. The data
     * will follow the TimelineBarData interface
     *
     */
    private _drawShifts() {
        if (this.shift.shifts) {
            // this.shifts.shifts SHOULD BE an array, but under certain
            // conditions it would return an object, likely due to a
            // resort and not resetting the array keys, but this helps
            // prevent any issues down the road
            const shifts = Object.keys(this.shift.shifts)
            shifts.forEach((item) => {
                const sub = <Array<TimelineBarData>>this.shift.shifts[item]
                Object.keys(sub).forEach((key) => {
                    this._drawSingleShift(<TimelineBarData>sub[key])
                })
            })
        }
    }

    /**
     * Build a single shift segment, if active set redraw status to true
     *
     * @param shiftSegment
     */
    private _drawSingleShift(shiftSegment: TimelineBarData) {

        const item = this.createBar(
            shiftSegment.start,
            shiftSegment.length,
            `${shiftSegment.action}-bar-${this.id}`,
            shiftSegment.barColor)

        if (shiftSegment.active === true) {
            this.redraw(item)
        }
    }

    /**
     * Append the shift detail "cascade"
     */
    private _appendCascade() {
        if (this.shift.shifts) {
            this.appendCascade()
        }
    }

    /**
     * Create the current time indicator arrow
     */
    private _appendIndicatorArrow() {
        if (this.dotw === this.currentDay()) {
            this.createIndicator()
            setInterval(() => {
                this.redrawIndicator()
            }, 3000)
        }

    }

    /**
     * If a shift segment bar overflows the pre-defined min/max, an icon
     * (ellipses) is inserted to the right of the frame
     *
     * classname (timeline-leakage-icon) defined in components.css
     */
    private _createLeakageIcon() {
        const span = document.createElement('span')
        span.innerHTML = `<i class="far fa-chevron-double-right"></i>`
        span.className = 'timeline-leakage-icon'
        return span
    }

    private _insertLeakageIcon() {
        const target = document.getElementById(`progress-container-${this.id}`)
        setTimeout(() => {
            target.appendChild(this._createLeakageIcon())
        }, 800)
    }

    /**
     * If the bar has extended past the defined max, insert the 'leak'
     * icon
     */
    private _checkForShiftBarLeakage() {
        if (!this.shift.shifts) return
        if (this._checkShiftsForLeakage()) {
            this._insertLeakageIcon()
        }
    }

    /**
     * checks that a bar has extended past the defined max value
     */
    private _checkShiftsForLeakage() {
        return Object.keys(this.shift.shifts).some((key) => {
            const curr = <Array<TimelineBarData>>this.shift.shifts[key]
            const clock = curr.filter(bar => bar.action === 'clockin')[0]
            const end = clock.start + clock.length
            const max = this.containerStart + this.hourRange
            return end > max
        })
    }

    redrawIndicator() {
        var d = new Date()
        var time = Number(d.getHours()) + d.getMinutes() / 60
        const diff = time - this.containerStart
        const xPos = ((diff * this.ppHr - 6) / this.barWidth) * 100
        this.indicator.style.left = `${xPos}%`
        if (!this.indicatorVisible(this.indicator, this.indicator.parentElement)) {
            this.indicator.style.opacity = 0
        }
    }

    /**
     * If the bar segment is active, set redraw
     */
    private redraw(target) {
        var d = new Date()
        if (this.currentStatus !== '0') {
            var time = Number(d.getHours()) + d.getMinutes() / 60
            var length = time - target.start
            let width = (((length * this.ppHr) / this.barWidth) * 10000) / 100
            target.style.width = `${width}%`
        }
        setTimeout(() => {
            this.redraw(target)
        }, 3000)
    }

    appendShiftContainer() {
        const string = `<div class="flex-row flex-start w-span mgb-1x">
                      <div class="date-box flex-col flex-center col-center">${this.dotw}</div>
                      <div class="bar-container" id="bar-container">
                        <div class="progress-container" id='progress-container-${this.id}'></div>
                        <div class="schedule-container" id='schedule-container-${this.id}'></div>
                      </div>
                    </div>`
        const target = document.getElementById('timeline-container')
        const div = document.createElement('div')
        div.setAttribute('class', 'shift-container')
        div.innerHTML = string
        target.insertBefore(div, target.childNodes[0])
        this.shiftContainer = div
    }

    drawGrid() {
        // grid class names
        const grid = {
            0: 'bar-bg-major',
            1: 'bar-bg-minor',
        }

        // grid is drawn over the bar container
        // define this as an object property and not a block scoped var as it's needed elsewhere
        this.barContainer = document.getElementById(
            'progress-container-' + this.id,
        )

        this.barLeft = this.barContainer.offsetLeft
        this.barWidth = this.barContainer.offsetWidth

        // define the ppHr (pixel/Hr) used to set the width of the bars/grid
        this.ppHr = this.barWidth / this.hourRange

        let unitWidth = this.barWidth / this.gridUnits

        let widthTest = (unitWidth / this.barContainer.offsetWidth) * 100

        let ii, div, x, time, innerSpan, currentHour, timeStr, outputTime
        let date = new Date()

        for (ii = 0; ii < this.gridUnits; ++ii) {
            div = document.createElement('div')

            // define class and width
            div.setAttribute('style', `width:${widthTest}%;`)

            // modulus operator , helps skip every other box to place whole hours on long grid lines
            time = Math.floor(this.barStart + ii / 2)
            currentHour = date.getHours()

            if (time < 12 || time >= 24) {
                timeStr = 'a'
            } else if (time >= 12 && time < 24) {
                timeStr = 'p'
            }

            if (time > 12 && time < 24) {
                outputTime = time - 12
            } else if (time >= 24) {
                outputTime = time - 24
            } else {
                outputTime = time
            }

            if (!(ii % 2)) {
                innerSpan = document.createElement('span')
                innerSpan.setAttribute(
                    'style',
                    'position:absolute;left:0px;text-align:left;font-size:.8rem;width:calc(100%);height:50%;border-right:1px solid var(--grid-line-dark);opacity:1;z-index:150;',
                )

                div.append(innerSpan)
            } else {
                // innerSpan = document.createElement('span');
                // innerSpan.setAttribute('style', 'position:absolute;left:0px;text-align:left;font-size:.8rem;width:calc(100%);height:50%;border-right:1px solid var(--grid-line-dark);opacity:1;');
                //    div.append(innerSpan);
            }

            div.id = time + (ii % 2) / 2
            div.setAttribute('class', grid[ii % 2])

            x = time + Math.round(date.getMinutes() / 60) / 2

            if (div.id == x && time === currentHour && this.shift.end === '') {
                // const pulse = document.createElement('div');
                // const pulseBG = document.createElement('div');
                // pulseBG.classList.add('pulseBG' );
                // pulse.classList.add('current-hour');
                // pulse.classList.add(this.shift.currentState)
                // div.appendChild(pulse);
                // div.appendChild(pulseBG);
            }
            // append the div
            this.barContainer.append(div)
        }
    }

    createBar(start, length, divId, color, targetDiv = this.barContainer) {
        //
        console.log("createBar: ", { start, length, divId, color, targetDiv })
        let div = document.createElement('div')

        let x = (((start - this.containerStart) * this.ppHr) / this.barWidth) * 100

        let width = ((length * this.ppHr) / this.barWidth) * 100

        div.id = `${divId}_${this.id}`

        if (divId.indexOf('clockin-bar') >= 0) {
            div.setAttribute('class', 'progress-bar-item clock-bar')
        } else {
            div.setAttribute('class', 'progress-bar-item')
        }
        div.setAttribute(
            'style',
            `left:${x}%;width:${width}%;background-color:${color};`,
        )

        targetDiv.appendChild(div)

        this[divId] = div
        this[divId].start = start
        if (this.currentStatus <= 1 || this.currentStatus != 0) {
            if (divId == 'clock-bar' && this.dotw === this.currentDay()) {
                this.redraw(div)
            }
        }

        return this[divId]
    }

    appendDataRow() {
        const div = document.createElement('div')
        div.id = 'data-row'
        div.classList.add('data-row')
        let unitWidth = this.barContainer.offsetWidth / this.gridUnits
        let widthTest, textAlign
        // let widthTest = (this.ppHr / this.barContainer.offsetWidth) * 100;
        if (window.innerWidth > 1200) {
            widthTest =
                -((this.ppHr / this.gridUnits / 2) * this.hourRange) / 2 - 0.9
            textAlign = 'right'
        } else if (window.innerWidth > 800) {
            widthTest = -((this.ppHr / this.gridUnits / 2) * this.hourRange) / 2
            textAlign = 'center'
        } else {
            textAlign = 'left'
        }

        for (let ii = 0; ii < this.gridUnits / 2; ++ii) {
            const span = document.createElement('span')

            div.setAttribute('style', 'position:relative; left:-5px;')
            span.setAttribute(
                'style',
                `position:relative;margin-top:2px;font-size:.8rem;width:100%;text-align:left;`,
            )
            // modulus operator , helps skip every other box to place whole hours on long grid lines
            let time = this.containerStart + ii
            // currentHour = date.getHours();
            let timeStr, outputTime
            if (time < 12 || time >= 24) {
                timeStr = 'a'
            } else if (time >= 12 && time < 24) {
                timeStr = 'p'
            }

            if (time > 12 && time <= 24) {
                outputTime = time - 12
            } else if (time > 24) {
                outputTime = time - 24
            } else {
                outputTime = time
            }

            if (outputTime === 0) {
                outputTime = 12
            }

            span.innerText = Math.floor(outputTime).toString() + timeStr

            if (time % 2) {
                // span.innerText= '';
                span.classList.add('clear')
                span.classList.add('nudge-rt-3')
                // span.classList.add('onbreak');
            } else {
                // span.classList.add('clockedin')
            }

            div.appendChild(span)
        }
        this.barContainer.parentNode.appendChild(div)
    }

    /**
     *
     */
    private appendCascade() {
        return Fetch.get(`/timeline_cascade/${this.empId}/${this.shift.shift_creation_date}`, {}, { html: 'HTML' })
            .then(r => this._handleCascadeResponse(r))
    }

    private _handleCascadeResponse(r: FetchResponseInterface) {
        if (r.status !== 'success') return this._runOnCascadeFailure(r)
        return this._runOnCascadeSuccess(r)
    }

    /**
     *
     * @param r
     */
    private _runOnCascadeSuccess(r: FetchResponseInterface) {
        const div = document.createElement('div')
        div.classList.add(`cascade-container`)
        div.id = `cascade-container-${this.id}`
        div.innerHTML = <string><any>r.data
        this.shiftContainer.appendChild(div)
        this.shiftContainer.addEventListener('click', (e) => {
            e.preventDefault()
            div.classList.toggle('flex')
        })
    }

    private _runOnCascadeFailure(r: FetchResponseInterface) { }

    currentDay() {
        const dowArr = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
        var d = new Date()
        var day = dowArr[d.getDay()]
        return day
    }

    createIndicator() {
        var d = new Date()
        var hh = d.getHours()
        var mm = d.getMinutes()
        var currentTime = Number(hh) + mm / 60
        const diff = currentTime - this.containerStart
        const xPos = ((diff * this.ppHr - 6) / this.barWidth) * 100
        const target = this.barContainer.parentElement
        const indicator = document.createElement('div')
        indicator.setAttribute('class', 'indicator')
        indicator.setAttribute(
            'style',
            `position:absolute;top:-6px;left:${xPos}%;z-index:300;`,
        )
        indicator.id = 'indicator'
        const arrow = document.createElement('div')
        arrow.setAttribute('class', 'indicator-arrow')
        arrow.classList.add('current-hour')
        const lineContainer = document.createElement('div')
        lineContainer.setAttribute('class', 'indicator-line-container')
        const line = document.createElement('div')
        line.setAttribute('class', 'indicator-line-left')
        const lineRt = document.createElement('div')
        lineRt.setAttribute('class', 'indicator-line-rt')
        lineRt.classList.add(this.currentStatus + '-border')
        lineContainer.appendChild(line)
        lineContainer.appendChild(lineRt)
        indicator.appendChild(arrow)
        indicator.appendChild(lineContainer)
        // // target = document.getElementById(x);
        this['indicator'] = indicator

        target.appendChild(indicator)

        if (!this.indicatorVisible(indicator, target)) {
            indicator.style.opacity = '0'
        }
    }

    indicatorVisible(node, parent) {
        const indicatorBounds = node.getBoundingClientRect()
        const targetBounds = parent.getBoundingClientRect()
        indicatorBounds.xCenter = indicatorBounds.left + indicatorBounds.width / 2

        if (
            indicatorBounds.xCenter < targetBounds.left ||
            indicatorBounds.xCenter > targetBounds.right
        ) {
            return false
        }

        return true
    }
}


interface TimelineData {
    current_status: number
    dotw: string
    max: number
    min: number
    shift_creation_date: string
    shifts: Array<any>
    schedule: Array<Array<TimelineBarData>>
}

interface TimelineBarData {
    action: string
    active: boolean
    barColor: string
    length: number
    start: number
}
