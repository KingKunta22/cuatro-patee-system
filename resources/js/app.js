import './bootstrap';
import './alerts';

// Gets ID and inputted password
const showValue = document.getElementById('showValue');
const hideValue = document.getElementById('hideValue');
const passwordValue = document.getElementById('password');

// Removes the hidden class (built-in with tailwind)
// and sets the password type="text" or type="password";
showValue.addEventListener('click', function() {
    showValue.classList.add('hidden');
    hideValue.classList.remove('hidden');
    passwordValue.setAttribute('type', 'text');
})

hideValue.addEventListener('click', function() {
    hideValue.classList.add('hidden');
    showValue.classList.remove('hidden');
    passwordValue.setAttribute('type', 'password');
})

