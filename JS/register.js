// Add any JavaScript specific to the register page here
// For example, form validation or additional functionality
console.log("Register page JavaScript loaded.");

document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');

  form.addEventListener('submit', function (event) {
      const username = form.querySelector('input[name="username"]').value;
      const password = form.querySelector('input[name="password"]').value;
      const confirmPassword = form.querySelector('input[name="confirmPassword"]').value;

      // Validate username
      if (!/^[a-zA-Z0-9-]+$/.test(username)) {
          alert("Username can only contain letters, numbers, and hyphens (-).");
          event.preventDefault();
      }

      // Validate password length
      if (password.length < 8 || password.length > 20) {
          alert("Password must be between 8 and 20 characters.");
          event.preventDefault();
      }

      // Validate password match
      if (password !== confirmPassword) {
          alert("Passwords do not match.");
          event.preventDefault();
      }
  });
});