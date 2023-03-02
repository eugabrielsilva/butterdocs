// Mobile sidenav menu
const sidenav = document.querySelector('section.menu');
function toggleSidenav() {
    sidenav.classList.toggle('show');
}

// Syntax highlighter
hljs.highlightAll();

// Light/dark mode initial setting
if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.querySelector('#theme-dark').removeAttribute('disabled');
    document.querySelector('#theme-light').setAttribute('disabled', '');
}

// Light/dark mode automatic change
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    if(event.matches) {
        document.querySelector('#theme-dark').removeAttribute('disabled');
        document.querySelector('#theme-light').setAttribute('disabled', '');
    } else {
        document.querySelector('#theme-dark').setAttribute('disabled', '');
        document.querySelector('#theme-light').removeAttribute('disabled');
    }
})