import axios from "axios";

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
            data.append('action', action);
        } else if (params instanceof FormData) {
            data = params;
            data.append('action', action);
        }

        return data;
    }
}