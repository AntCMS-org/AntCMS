import "./TinyZoom";

import "bootstrap/js/dist/base-component";
import "bootstrap/js/dist/dropdown";
import "bootstrap/js/dist/collapse";

import "prismjs";
import 'prismjs/components/prism-markup-templating';
import "prismjs/components/prism-yaml";
import "prismjs/components/prism-php";
import "prismjs/components/prism-markup";
import "prismjs/components/prism-markdown";
import "prismjs/components/prism-bash";

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.api-form').forEach((element) => {
        element.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(element);
            const plainData = Object.fromEntries(formData.entries());
            const data = JSON.stringify(plainData);

            fetch(element.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: data,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then((data) => {
                    // Handle redirect or reload
                    const redirectUrl = element.dataset.redirect;
                    const shouldReload = element.dataset.reload === "true";

                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else if (shouldReload) {
                        window.location.reload();
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.api-link').forEach((element) => {
        element.addEventListener('click', function (event) {
            event.preventDefault();

            fetch(element.href, {
                method: 'GET',
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then((data) => {
                    // Handle redirect or reload
                    const redirectUrl = element.dataset.redirect;
                    const shouldReload = element.dataset.reload === "true";

                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else if (shouldReload) {
                        window.location.reload();
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
        });
    });
});