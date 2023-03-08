function switchTheme() {
    const mode = window.localStorage.getItem('mode');
    !mode || mode === 'light'
        ? setTheme('dark')
        : setTheme('light');
}

function setTheme(theme) {
    const html = document.getElementsByTagName('html')[0];
    html.dataset.dataBsTheme = theme;
    window.localStorage.setItem('mode', theme);
}

setTheme(window.localStorage.getItem('mode'));