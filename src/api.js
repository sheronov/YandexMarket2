import axios from "axios";

export default {
    post(action, params) {
        return axios.request({
            method: 'POST',
            data: this.prepareData(params, action)
        })
            .then(response => {
                if (('success' in response.data) && !response.data.success) {
                    throw new Error(response.data.message || 'Ошибка');
                }
                return response;
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