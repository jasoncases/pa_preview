import { Fetch } from "../../../../System/Components/Fetch/Fetch.js";

export class Action {
    public static async submit(code: string) {
        return Fetch.store("/login", {
            pin: code,
            email: null,
            pass: null,
        });
    }
}
