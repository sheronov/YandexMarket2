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
    post(action, params = {}) {
        return axios.request({
            method: 'POST',
            data: this.prepareData(params, action)
        })
            .then(response => {
                if (Object.prototype.hasOwnProperty.call(response.data, 'success') && !response.data.success) {
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
            this.buildFormData(data, params);
        } else if (params instanceof FormData) {
            data = params;
        }

        data.append('action', action);
        return data;
    },
    buildFormData(formData, data, parentKey) {
        if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
            Object.keys(data).forEach(key => {
                this.buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
            });
        } else {
            const value = data == null ? '' : data;

            formData.append(parentKey, value);
        }
    }
}