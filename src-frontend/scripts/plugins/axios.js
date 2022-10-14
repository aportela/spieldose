import axios from 'axios';

export default {
    install: (app, options) => {
        let axiosInstance = axios.create(options);
        axiosInstance.interceptors.request.use((config) => {
            const jwt = app.config.globalProperties.$spieldoseLocalStorage.get('jwt');
            if (jwt) {
                config.headers["SPIELDOSE-JWT"] = jwt;
            }
            return (config);
        }, (error) => {
            return Promise.reject(error);
        });
        axiosInstance.interceptors.response.use((response) => {
            // warning: axios lowercase received header names
            const apiResponseJWT = response.headers["spieldose-jwt"] || null;
            if (apiResponseJWT) {
                if (apiResponseJWT && apiResponseJWT != jwt) {
                    app.config.globalProperties.$spieldoseLocalStorage.set("jwt", apiResponseJWT);
                }
            }
            return response;
        }, (error) => {
            // helper for checking invalid fields on api response
            error.isFieldInvalid = function (fieldName) {
                return (error.response.data.invalidOrMissingParams.indexOf(fieldName) > -1);
            }
            return Promise.reject(error);
        });
        app.config.globalProperties.$axios = axiosInstance;
    }
}
