import  Toasted from 'vue-toasted'
import Vue from 'vue'
Vue.use(Toasted);

export const showSuccessToast = (message) => {
    Vue.toasted.show(message, {
        theme: "bubble",
        position: "bottom-right",
        duration : 4000,
        type: 'success'
    });
};

export const showErrorToast = (message) => {
    Vue.toasted.show(message, {
        theme: "bubble",
        position: "bottom-right",
        duration : 4000,
        type: 'error'
    });
};

export const showInfoToast = (message) => {
    Vue.toasted.show(message, {
        theme: "bubble",
        position: "bottom-right",
        duration : 6000,
        type: 'info'
    });
};
