// Form switching functionality
const loginForm = document.querySelector('.login');
const signupForm = document.querySelector('.signup');
const showSignupButton = document.getElementById('showSignup');
const showLoginButton = document.getElementById('showLogin');

function switchForms(hideForm, showForm) {
    hideForm.classList.add('hidden');
    showForm.classList.remove('hidden');
}

showSignupButton.addEventListener('click', (e) => {
    e.preventDefault();
    switchForms(loginForm, signupForm);
});

showLoginButton.addEventListener('click', (e) => {
    e.preventDefault();
    switchForms(signupForm, loginForm);
});

// Form submission handling
document.getElementById('loginForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    console.log('Login attempt:', Object.fromEntries(formData));
    // Add your login logic here
});

document.getElementById('signupForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    console.log('Signup attempt:', Object.fromEntries(formData));
    // Add your signup logic here
});

// Add ripple effect to buttons
const buttons = document.querySelectorAll('.btn');
buttons.forEach(button => {
    button.addEventListener('click', function(e) {
        const x = e.clientX - e.target.offsetLeft;
        const y = e.clientY - e.target.offsetTop;
        
        const ripple = document.createElement('span');
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});