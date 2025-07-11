document.getElementById('applyJobForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const messageContainer = document.createElement('div');

    fetch('api/apply_job.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageContainer.className = 'alert alert-success';
            messageContainer.textContent = 'Application submitted successfully!';
            form.reset();
        } else {
            messageContainer.className = 'alert alert-danger';
            messageContainer.textContent = data.message || 'An error occurred. Please try again.';
        }
        form.prepend(messageContainer);
    })
    .catch(error => {
        messageContainer.className = 'alert alert-danger';
        messageContainer.textContent = 'An error occurred. Please try again.';
        form.prepend(messageContainer);
        console.error('Error:', error);
    });
});