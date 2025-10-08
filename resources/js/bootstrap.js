import axios from 'axios';
window.axios = axios;
axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
