/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
//import '../styles/app.css';

// start the Stimulus application
//import '../../assets/bootstrap.js';

function displayTricks() {
    const tricks = document.getElementById('hidden-tricks');
    const arrow = document.getElementById('arrow-up');
    const btn = document.getElementById('btn-display');
    if (tricks.style.display === 'none' && arrow.style.display === 'none') {
        tricks.style.display = 'block';
        arrow.style.display = 'block';
        btn.style.display = 'none';
    }
}