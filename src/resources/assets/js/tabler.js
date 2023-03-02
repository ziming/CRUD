// Handle theming light|dark
const mode = window.localStorage.getItem('mode'),
    body = document.getElementsByTagName('body')[0];

function switchTheme() {
    !mode || mode === 'light'
        ? setTheme('dark')
        : setTheme('light');
}

function setTheme(theme) {
    const previousTheme = theme === 'light' ? 'dark' : 'light';
    body.classList.remove('theme-' + previousTheme);
    body.classList.add('theme-' + theme);
    window.localStorage.setItem('mode', theme);
}

setTheme(mode);