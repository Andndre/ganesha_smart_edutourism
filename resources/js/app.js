import Swal from 'sweetalert2';
import { getBalineseInfo, getMonthRahinan } from './balinese-calendar';

window.Swal = Swal.mixin({
    heightAuto: false
});

window.getBalineseInfo = getBalineseInfo;
window.getMonthRahinan = getMonthRahinan;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
