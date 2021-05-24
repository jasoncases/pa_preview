import { ExtendError } from "./ExtendError.js"
import { ExtendErrorMissingKey } from "./ExtendErrorMissingKey.js"
import { ElementNotFound } from "./System/Components/ElementNotFound.js"
import { CloseTask } from "./Tasks/General/CloseTask.js"
import { ChangeAssignees } from "./Tasks/TaskGui/ChangeAssignees.js"
import { CloneFromPerpetual } from "./Tasks/TaskGui/CloneFromPerpetual.js"
import { PromoteToPerpetual } from "./Tasks/TaskGui/PromoteToPerpetual.js"
import { RemoveAssignees } from "./Tasks/TaskGui/RemoveAssignees.js"

const srcMap = {
    RemoveAssignees: RemoveAssignees,
    ChangeAssignees: ChangeAssignees,
    CloneFromPerpetual: CloneFromPerpetual,
    PromoteToPerpetual: PromoteToPerpetual,
    CloseTask: CloseTask,
    Error: ExtendError,
    ExtendErrorMissingKey: ExtendErrorMissingKey,
    ElementNotFound: ElementNotFound
}
export class Throw {
    public static err(key: string = "Error", msg: string = null) {
        if (!key) key = "Error"
        if (Object.keys(srcMap).indexOf(key) === -1) {
            msg = key
            key = 'ExtendErrorMissingKey'
        }
        throw new srcMap[key](msg)
    }
}
