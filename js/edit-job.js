document.addEventListener('DOMContentLoaded', function() {
    const salaryType = document.getElementById('salary_type');
    const salaryFields = document.getElementById('salaryFields');
    const editJobForm = document.getElementById('editJobForm');
    const messageContainer = document.getElementById('message-container');
    const addQuestionBtn = document.getElementById('add-question-btn');
    const questionsContainer = document.getElementById('questions-container');

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

    setTimeout(() => {
        if (jobData.salary_type === 'salary') {
            document.getElementById('annual_salary_min').value = jobData.annual_salary_min;
            document.getElementById('annual_salary_max').value = jobData.annual_salary_max;
        } else if (jobData.salary_type === 'hourly') {
            document.getElementById('hourly_rate_min').value = jobData.hourly_rate_min;
            document.getElementById('hourly_rate_max').value = jobData.hourly_rate_max;
        }
    }, 0);

    if (jobData.questions && questionsContainer) {
        jobData.questions.forEach(question => {
            const questionInput = document.createElement('div');
            questionInput.className = 'input-group mb-2';
            questionInput.innerHTML = `
                <input type="text" name="questions[]" class="form-control" value="${question.question_text}">
                <button type="button" class="btn btn-outline-danger remove-question-btn">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            questionsContainer.appendChild(questionInput);
        });
    }

    if (editJobForm) {
        editJobForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageContainer.innerHTML = '';

            const formData = new FormData(editJobForm);
            formData.append('description', descriptionEditor.root.innerHTML);

            fetch('api/update_job.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                const { status, body } = res;
                let alertClass = 'alert-danger';
                if (status === 200) {
                    alertClass = 'alert-success';
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

    if (isRecurringCheckbox) {
        isRecurringCheckbox.addEventListener('change', function() {
            recurrenceOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
});