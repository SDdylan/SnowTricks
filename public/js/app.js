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

//add new items (media form)
const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

    const item = document.createElement('li');

    item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );

    collectionHolder.appendChild(item);

    collectionHolder.dataset.index++;

    addMediaFormDeleteLink(item);
};

const addMediaFormDeleteLink = (item) => {
    const removeFormButton = document.createElement('button');
    removeFormButton.innerText = 'Supprimer ce Media';
    removeFormButton.className += 'btn btn-warning';

    item.append(removeFormButton);

    removeFormButton.addEventListener('click', (e) => {
        e.preventDefault();
        // remove the li for the tag form
        item.remove();
    });
}

document.querySelectorAll('.add_item_link').forEach(btn => {
    btn.addEventListener("click", addFormToCollection)
});

document
    .querySelectorAll('div.panel').forEach((tag) => {
        addMediaFormDeleteLink(tag)
    })