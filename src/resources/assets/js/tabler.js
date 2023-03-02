function switchTheme() {
    const mode = window.localStorage.getItem('mode');
    !mode || mode === 'light'
        ? setTheme('dark')
        : setTheme('light');
}

function setTheme(theme) {
    const body = document.getElementsByTagName('body')[0];
    const previousTheme = theme === 'light' ? 'dark' : 'light';

    body.classList.remove('theme-' + previousTheme);
    body.classList.add('theme-' + theme);

    window.localStorage.setItem('mode', theme);
}

setTheme(window.localStorage.getItem('mode'));