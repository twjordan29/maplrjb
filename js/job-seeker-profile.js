document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = '';

    fetch('api/update_job_seeker_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageContainer.className = 'alert alert-success';
            messageContainer.textContent = 'Profile updated successfully!';
        } else {
            messageContainer.className = 'alert alert-danger';
            messageContainer.textContent = data.message || 'An error occurred. Please try again.';
        }
    })
    .catch(error => {
        messageContainer.className = 'alert alert-danger';
        messageContainer.textContent = 'An error occurred. Please try again.';
        console.error('Error:', error);
    });
});