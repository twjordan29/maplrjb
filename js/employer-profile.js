document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const messageContainer = document.getElementById('message-container');

    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageContainer.innerHTML = ''; // Clear previous messages

            // Use FormData to handle file uploads
            const formData = new FormData(profileForm);

            fetch('api/employer_profile.php', {
                method: 'POST',
                body: formData // No 'Content-Type' header, browser sets it automatically for FormData
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                const { status, body } = res;
                let alertClass = 'alert-danger';
                if (status === 200) {
                    alertClass = 'alert-success';
                    // Optionally, you could refresh parts of the page to show new images
                }
                
                const message = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                    ${body.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                 </div>`;
                messageContainer.innerHTML = message;
                window.scrollTo(0, 0); // Scroll to top to show message
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
    function setupImageUpload(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const display = input.nextElementSibling;

        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    display.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    setupImageUpload('logo');
    setupImageUpload('header');
});