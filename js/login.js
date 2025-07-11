document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const messageContainer = document.getElementById('message-container');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageContainer.innerHTML = ''; // Clear previous messages

            const email = document.getElementById('inputEmail').value;
            const password = document.getElementById('inputPassword').value;

            const formData = {
                email: email,
                password: password
            };

            fetch('api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                const { status, body } = res;
                
                if (status === 200) {
                    // Redirect to dashboard on successful login
                    window.location.href = 'dashboard.php';
                } else {
                    const message = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        ${body.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                     </div>`;
                    messageContainer.innerHTML = message;
                }
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