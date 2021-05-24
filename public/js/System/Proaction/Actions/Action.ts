import { Fetch } from '../../Components/Fetch/Fetch.js'

export class Action {

    public static async getClientSetting(key: string) {
        return Fetch.get('/api/client_settings', { type: 'client', key: key })
    }

    public static async getSystemSetting(type: string, key: string) {
        return Fetch.get('/api/system_settings', { type: type, key: key })
    }


}
