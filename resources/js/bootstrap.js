import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configure axios to send credentials (cookies) with requests
axios.defaults.withCredentials = true;

// Get CSRF token from cookie and set it as default header
function getCSRFToken() {
    const name = 'XSRF-TOKEN=';
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return null;
}

// Set CSRF token if available
const csrfToken = getCSRFToken();
if (csrfToken) {
    axios.defaults.headers.common['X-XSRF-TOKEN'] = csrfToken;
}
