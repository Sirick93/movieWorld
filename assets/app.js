/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// Styles
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';

// JS
//import 'jquery';
//import 'bootstrap/dist/js/bootstrap.bundle.min.js';
const initialize = () => {
    $('#like-btn, #hate-btn').on('click', function () {
        let value = $(this).data("value");
        let movie = $(this).data("movie");
        let csrfToken = $(this).data('token');

        fetch('/vote/new', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ value: value, movie: movie, csrfToken: csrfToken })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then(data => {
            //console.log(data);
            $('#likes').text(data.likes);
            $('#hates').text(data.hates);
            $('.alert-box').show();
            $('.alert-box').html('<div class="alert alert-primary">Movie rating '+data.action+'</div>');
            setTimeout(function() {
                $('.alert-box').fadeOut();
            }, 5000);
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An Error occurred. Contact the administrator.");
        });
    });
}
document.addEventListener("DOMContentLoaded", () => initialize());