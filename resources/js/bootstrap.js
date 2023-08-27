window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */


import Echo from 'laravel-echo'
import Pusher from "pusher-js"
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'fa306df2a43609a08a4b',
    cluster: 'eu',
    forceTLS: true,
    authEndpoint: 'http://127.0.0.1:8000/api/custom/broadcasting/auth/a',
    encrypted: false,
    auth: {
        headers: {
            Authorization: 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xOTIuMTY4LjEuMTFcL0lULUNvcm5lclwvaGVyYVwvcHVibGljXC9hcGlcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNjU0MTY0NzI2LCJuYmYiOjE2NTQxNjQ3MjYsImp0aSI6InpCdm04UTNBWTRPMTdNRHQiLCJzdWIiOjEsInBydiI6IjFkMGEwMjBhY2Y1YzRiNmM0OTc5ODlkZjFhYmYwZmJkNGU4YzhkNjMifQ.OePdw_imolJLa_hkoxoKeeae5JB4eA4RB0vhGIsxqJg',
            type: 'customer'
            // Authorization: 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xOTIuMTY4LjEuMTFcL0lULUNvcm5lclwvaGVyYVwvcHVibGljXC9hcGlcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNjU0MTc5MTA5LCJuYmYiOjE2NTQxNzkxMDksImp0aSI6IjI4TUZqTkdHZnlDalhzWTkiLCJzdWIiOjMsInBydiI6IjBmNmMwNWUxNzIzYWI5ZTM5YmJjMWMwZTkxMmEzZWJhN2FjZTdmOTcifQ.CCpNDNoyCsM9ufuAvImlzaOF3FKtP9mihqhKqGWBqJI'
        },
    },
});

// fetch('http://192.168.1.11/IT-Corner/hera/public/api/auth/chat/2',{
//     headers: {
//         'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xOTIuMTY4LjEuMTFcL0lULUNvcm5lclwvaGVyYVwvcHVibGljXC9hcGlcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNjU0MTY0NzI2LCJuYmYiOjE2NTQxNjQ3MjYsImp0aSI6InpCdm04UTNBWTRPMTdNRHQiLCJzdWIiOjEsInBydiI6IjFkMGEwMjBhY2Y1YzRiNmM0OTc5ODlkZjFhYmYwZmJkNGU4YzhkNjMifQ.OePdw_imolJLa_hkoxoKeeae5JB4eA4RB0vhGIsxqJg',
//         'type': 'customer'
//     },
//     method: 'POST',
//     body:
//         {
//             numOfPage:'1',
//             numOfRows:'10'
//         }
// })
//     .then(response => response.json()).then(data => {
//     var response =  data['data']['response'];
//     console.log(response);
//     for (var i = 0;i < response.length;i++)
//     {
//         if (response[i]['content_type'] == 'text')
//         {
//             const para = document.createElement("p");
//             const node = document.createTextNode(response[i]['content']);
//             para.appendChild(node);
//             const element = document.getElementById("app");
//             element.appendChild(para);
//         }else
//         {
//             let img = document.createElement("img");
//             img.src = response[i]['content'];
//             img.style.border = "10px solid orange";
//             img.style.borderRadius = "10px";
//
//             const element = document.getElementById("app");
//             element.appendChild(img);
//         }
//         const element = document.getElementById("app");
//         const myvar = document.createElement('br');
//         element.appendChild(myvar);
//     }
//     })
//     .catch(error => {
//         // handle the error
//     });


var channel = window.Echo.join('21');

channel.here((users) => {
    console.log(users);
});



channel.listen('Messaging', function(data) {

    console.log(data);
});

