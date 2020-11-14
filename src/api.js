import axios from "axios";

let ym2Config = window.ym2Config || {
    modAuth: process.env.VUE_APP_MOD_AUTH || '',
    apiUrl: process.env.VUE_APP_API_URL || '',
    lang: {}
};

axios.defaults.baseURL = ym2Config.apiUrl;
axios.defaults.headers.common['modAuth'] = ym2Config.modAuth;

if (process.env.VUE_APP_COOKIE) {
    axios.defaults.headers.common['modCookie'] = process.env.VUE_APP_COOKIE;
}

export default {
    post(action, params) {
        let data = this.prepareData(params, action);
        return axios.request({
            method: 'POST',
            data
        })
    },
    prepareData(params, action) {
        let data;
        if ((typeof params === 'object') && !(params instanceof FormData)) {
            data = new FormData();
            for (const key in params) {
                if (Object.prototype.hasOwnProperty.call(params, key)) {
                    data.append(key, params[key]);
                }
            }
        } else {
            data = params;
        }
        data.append('action', action);
        return data;
    }
}