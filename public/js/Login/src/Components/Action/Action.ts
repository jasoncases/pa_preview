import { isJSON, FetchResponseInterface, handleFetchResponse } from '../../../../System/Lib/Lib.js'
import { Fetch } from '../../../../System/Components/Fetch/Fetch.js'
import { Request } from '../../../../System/Components/Fetch/Request.js'

export class Action {
    public static async submit(
        email: string,
        pass: string,
    ): Promise<FetchResponseInterface> {
        const request = await fetch('/login', {
            method: 'POST',
            mode: 'cors',
            credentials: 'include', // without this safari will not save the session data after the redirect
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': Request.getScrfToken(),
                'Content-type': 'application/x-www-form-urlencoded',
            },
            body: `pin=&email=${email}&pass=${pass}`,
        })
        return await request.text().then(response => {
            return handleFetchResponse(request, response, 'JSON', false)
        })
    }

    public static async passwordReset(email: string, pass: string): Promise<any> {
        console.log('pass:', pass)
        console.log('email:', email)
        return Fetch.secure('/emp_pass', {
            email: email,
            pass: pass,
        })
    }
}
