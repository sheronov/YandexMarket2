import axios from "axios";

export class ErrorException extends Error {
    constructor(response) {
        super((response && response.message) || 'Ошибка');
        this.response = response;
    }

    getMessage() {
        return this.message;
    }
}


export class ValidationError extends ErrorException {
    getErrors() {
        return (this.response && this.response.data) || [];
    }
}

export default {
    post(action, params) {
        return axios.request({
            method: 'POST',
            data: this.prepareData(params, action)
        })
            .then(response => {
                if (('success' in response.data) && !response.data.success) {
                    if (response.data.data && response.data.data.length) {
                        throw new ValidationError(response.data);
                    } else {
                        throw new ErrorException(response.data);
                    }
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