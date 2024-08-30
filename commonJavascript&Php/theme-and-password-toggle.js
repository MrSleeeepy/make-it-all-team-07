// functionality for the passwords visible or not visible
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "visibility";
    } else {
        input.type = "password";
        icon.textContent = "visibility_off";
    }
}

// Dark/light mode toggle functionality to switch themes and store the user's preference
document.addEventListener('DOMContentLoaded', function() {
    const darkSwitch = document.getElementById('darkSwitch');
    const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;
    if (currentTheme) {
        document.body.setAttribute('data-theme', currentTheme);

        if (currentTheme === 'dark') {
            darkSwitch.checked = true;
        }
    }
    darkSwitch.addEventListener('click', function() {
        document.body.classList.toggle('dark-theme');
        let theme = 'light';
        if (document.body.classList.contains('dark-theme')) {
            theme = 'dark';
        }
        document.body.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    });
});