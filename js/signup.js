document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    const messageContainer = document.getElementById('message-container');

    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageContainer.innerHTML = ''; // Clear previous messages

            const firstName = document.getElementById('inputFirstName').value;
            const lastName = document.getElementById('inputLastName').value;
            const userType = document.getElementById('inputUserType').value;
            const email = document.getElementById('inputEmail').value;
            const password = document.getElementById('inputPassword').value;
            const passwordConfirm = document.getElementById('inputPasswordConfirm').value;

            const formData = {
                first_name: firstName,
                last_name: lastName,
                user_type: userType,
                email: email,
                password: password,
                password_confirm: passwordConfirm
            };

            fetch('api/signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                const { status, body } = res;
                let alertClass = 'alert-danger';
                if (status === 201) {
                    alertClass = 'alert-success';
                    // Redirect to login page after a short delay to allow the user to see the message
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000); // 2-second delay
                }
                
                const message = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                    ${body.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                 </div>`;
                messageContainer.innerHTML = message;
            })
            .catch(error => {
                console.error('Error:', error);
                const message = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    An unexpected error occurred. Please try again.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                 </div>`;
                messageContainer.innerHTML = message;
            });
        });
    }
});