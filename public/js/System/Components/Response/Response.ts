import { Fetch } from "../Fetch/Fetch.js";
import { Slider } from "../Alert/Slider.js";

interface ResponseObj {
    status: string;
    message: string;
    allowedRoutes: Array<string>;
    expiration: number;
}

export class Response {
    /**
     * Gets a FlashAlert message from the server, using either the
     * Controller::message() method or FlashAlert class.
     * Passes a message/status combo to the session, then /response route
     * fetches that one value from the session
     */
    public static async get() {
        const response = new Response();
        return response._get();
    }

    /**
     * Use this when you want to handle a message on the client side. You
     * could also just use the Slider class, but Response.put() included
     * for consistency, when dealing with Async responses.
     * @param status
     * @param message
     */
    public static put(status: string, message: string, delay: number = 4000) {
        Slider.create(status, message, delay);
    }

    private _get() {
        return Fetch.get("/api/response").then((response) => {
            console.log("response:", response);
            if (response.status !== "success") return false;
            if (!response.data.flashAlert) return false;
            const alert = <ResponseObj>response.data.flashAlert;
            if (!this._isRouteAllowed(alert.allowedRoutes)) return;
            if (!this._isMessageFresh(alert.expiration)) return;
            Slider.create(alert.status, alert.message);
            return true;
        });
    }

    private _isRouteAllowed(allowedRoutes: Array<string>): boolean {
        // if there are no allowedRoutes restrictions, play the alert
        if (!allowedRoutes) return true;
        if (!Array.isArray(allowedRoutes)) return true;
        if (allowedRoutes.length <= 0) return true;
        const url = window.location.href;
        return allowedRoutes.some((rte) => {
            return url.search(rte) >= 0;
        });
    }

    private _isMessageFresh(expiration: number): boolean {
        const now = Date.now();
        const diff = now - expiration;
        console.log({ now, diff, expiration });
        if (Date.now() > expiration) return;
        return true;
    }
}
