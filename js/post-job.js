document.addEventListener('DOMContentLoaded', function() {
    const salaryType = document.getElementById('salary_type');
    const salaryFields = document.getElementById('salaryFields');
    const postJobForm = document.getElementById('postJobForm');
    const messageContainer = document.getElementById('message-container');
    const addQuestionBtn = document.getElementById('add-question-btn');
    const questionsContainer = document.getElementById('questions-container');

    const recurrenceSection = document.getElementById('recurrence-section');
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurrenceOptions = document.getElementById('recurrence-options');

    const descriptionEditor = new Quill('#description', {
        theme: 'snow'
    });

    const salaryFieldTemplates = {
        salary: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="annual_salary_min" class="form-label">Annual Salary (Minimum)</label>
                    <input type="number" class="form-control" id="annual_salary_min" name="annual_salary_min" step="1000">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="annual_salary_max" class="form-label">Annual Salary (Maximum)</label>
                    <input type="number" class="form-control" id="annual_salary_max" name="annual_salary_max" step="1000">
                </div>
            </div>`,
        hourly: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="hourly_rate_min" class="form-label">Hourly Rate (Minimum)</label>
                    <input type="number" class="form-control" id="hourly_rate_min" name="hourly_rate_min" step="0.50">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="hourly_rate_max" class="form-label">Hourly Rate (Maximum)</label>
                    <input type="number" class="form-control" id="hourly_rate_max" name="hourly_rate_max" step="0.50">
                </div>
            </div>`,
        commission: '',
        volunteer: ''
    };

    function updateSalaryFields() {
        const selectedType = salaryType.value;
        salaryFields.innerHTML = salaryFieldTemplates[selectedType];
    }

    salaryType.addEventListener('change', updateSalaryFields);
    updateSalaryFields();

    if (postJobForm) {
        postJobForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageContainer.innerHTML = '';

            const formData = new FormData(postJobForm);
            formData.append('description', descriptionEditor.root.innerHTML);

            const questionInputs = questionsContainer.querySelectorAll('input[name="questions[]"]');
            questionInputs.forEach(input => {
                formData.append('questions[]', input.value);
            });

            fetch('api/post_job.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                const { status, body } = res;
                let alertClass = 'alert-danger';
                if (status === 200) {
                    alertClass = 'alert-success';
                    postJobForm.reset();
                    updateSalaryFields();
                    descriptionEditor.root.innerHTML = '';
                    if (questionsContainer) {
                        questionsContainer.innerHTML = '';
                    }
                }
                
                const message = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                    ${body.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                 </div>`;
                messageContainer.innerHTML = message;
                window.scrollTo(0, 0);
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

    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', () => {
            const questionInput = document.createElement('div');
            questionInput.className = 'input-group mb-2';
            questionInput.innerHTML = `
                <input type="text" name="questions[]" class="form-control" placeholder="Enter your question">
                <button type="button" class="btn btn-outline-danger remove-question-btn">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            questionsContainer.appendChild(questionInput);
        });
    }

    if (questionsContainer) {
        questionsContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-question-btn')) {
                e.target.closest('.input-group').remove();
            }
        });
    }

    // Show recurrence options if premium/verified
    if (recurrenceSection) {
        // This is a bit of a hack, but we'll check if the screening questions are visible
        if (document.getElementById('questions-container')) {
            recurrenceSection.style.display = 'block';
        }

        isRecurringCheckbox.addEventListener('change', function() {
            recurrenceOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
});