document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profile-form');
    const skillsContainer = document.getElementById('skills-container');
    const experienceContainer = document.getElementById('experience-container');
    const educationContainer = document.getElementById('education-container');
    const addSkillBtn = document.getElementById('add-skill-btn');
    const skillInput = document.getElementById('skill-input');
    const addExperienceBtn = document.getElementById('add-experience-btn');
    const addEducationBtn = document.getElementById('add-education-btn');
    const experienceTemplate = document.getElementById('experience-template');
    const educationTemplate = document.getElementById('education-template');
    const salaryType = document.getElementById('salary_type');

    let skills = [];

    const headlineInput = document.getElementById('headline');
    const headlineCounter = document.getElementById('headline-counter');

    headlineInput.addEventListener('input', () => {
        const remaining = 100 - headlineInput.value.length;
        headlineCounter.textContent = `${remaining}/100`;
    });

    async function fetchProfile() {
        try {
            const response = await fetch('api/get_candidate_profile.php');
            if (!response.ok) {
                if (response.status === 404) {
                    // Profile doesn't exist, so we can just show the blank form.
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const profile = await response.json();
            populateForm(profile);
        } catch (error) {
            console.error("Could not fetch profile:", error);
        }
    }

    function populateForm(profile) {
        form.headline.value = profile.headline || '';
        form.location_city.value = profile.location_city || '';
        form.location_province.value = profile.location_province || '';
        
        if (profile.availability) {
            const availabilityValues = profile.availability.split(',');
            document.querySelectorAll('input[name="availability[]"]').forEach(checkbox => {
                if (availabilityValues.includes(checkbox.value)) {
                    checkbox.checked = true;
                }
            });
        }

        form.summary.value = profile.summary || '';
        form.experience_years.value = profile.experience_years || '';
        form.education_level.value = profile.education_level || '';
        form.salary_type.value = profile.salary_type || 'annual';
        form.desired_salary_min.value = profile.desired_salary_min || '';
        form.desired_salary_max.value = profile.desired_salary_max || '';
        form.linkedin_url.value = profile.linkedin_url || '';
        form.portfolio_url.value = profile.portfolio_url || '';
        form.is_remote.checked = !!profile.is_remote;
        form.is_searchable.checked = !!profile.is_searchable;

        if (profile.skills) {
            skills = profile.skills;
            renderSkills();
        }
        if (profile.experience) {
            profile.experience.forEach(exp => addExperienceItem(exp));
        }
        if (profile.education) {
            profile.education.forEach(edu => addEducationItem(edu));
        }
    }

    function renderSkills() {
        skillsContainer.innerHTML = '';
        skills.forEach(skill => {
            const skillTag = document.createElement('span');
            skillTag.className = 'badge bg-secondary me-1';
            skillTag.textContent = skill.name;
            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn-close btn-close-white ms-1';
            removeBtn.style.fontSize = '0.6em';
            removeBtn.onclick = () => {
                skills = skills.filter(s => s.id !== skill.id);
                renderSkills();
            };
            skillTag.appendChild(removeBtn);
            skillsContainer.appendChild(skillTag);
        });
    }

    const skillSuggestionsContainer = document.getElementById('skill-suggestions-container');

    skillInput.addEventListener('input', async () => {
        const term = skillInput.value.trim();
        if (term.length < 2) {
            skillSuggestionsContainer.innerHTML = '';
            skillSuggestionsContainer.style.display = 'none';
            return;
        }

        const response = await fetch(`api/get_skills.php?term=${term}`);
        const suggestions = await response.json();

        if (suggestions.length > 0) {
            skillSuggestionsContainer.style.display = 'block';
            skillSuggestionsContainer.innerHTML = suggestions.map(s => `
                <div class="suggestion-item" data-id="${s.id}" data-name="${s.name}">
                    ${s.name} <span class="suggestion-type">(${s.category})</span>
                </div>
            `).join('');
        } else {
            skillSuggestionsContainer.style.display = 'none';
        }
    });

    skillSuggestionsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('suggestion-item')) {
            const skillId = parseInt(e.target.dataset.id);
            const skillName = e.target.dataset.name;
            if (!skills.find(s => s.id === skillId)) {
                skills.push({ id: skillId, name: skillName });
                renderSkills();
            }
            skillInput.value = '';
            skillSuggestionsContainer.innerHTML = '';
            skillSuggestionsContainer.style.display = 'none';
        }
    });

    addSkillBtn.addEventListener('click', () => {
        const skillName = skillInput.value.trim();
        if (skillName && !skills.find(s => s.name.toLowerCase() === skillName.toLowerCase())) {
            // This will now only be used for skills not in the database.
            // The backend will need to handle creating the skill and getting an ID.
            skills.push({ id: null, name: skillName });
            renderSkills();
            skillInput.value = '';
        }
    });

    function addExperienceItem(exp = {}) {
        const templateContent = experienceTemplate.content.cloneNode(true);
        const item = templateContent.querySelector('.experience-item');
        item.querySelector('[name="exp_job_title[]"]').value = exp.job_title || '';
        item.querySelector('[name="exp_company[]"]').value = exp.company || '';
        item.querySelector('[name="exp_start_date[]"]').value = exp.start_date || '';
        item.querySelector('[name="exp_end_date[]"]').value = exp.end_date || '';
        item.querySelector('[name="exp_description[]"]').value = exp.description || '';
        item.querySelector('.remove-item-btn').onclick = () => item.remove();
        experienceContainer.appendChild(templateContent);
    }

    function addEducationItem(edu = {}) {
        const templateContent = educationTemplate.content.cloneNode(true);
        const item = templateContent.querySelector('.education-item');
        item.querySelector('[name="edu_school[]"]').value = edu.school || '';
        item.querySelector('[name="edu_degree[]"]').value = edu.degree || '';
        item.querySelector('[name="edu_field_of_study[]"]').value = edu.field_of_study || '';
        item.querySelector('[name="edu_start_year[]"]').value = edu.start_year || '';
        item.querySelector('[name="edu_end_year[]"]').value = edu.end_year || '';
        item.querySelector('.remove-item-btn').onclick = () => item.remove();
        educationContainer.appendChild(templateContent);
    }

    addExperienceBtn.addEventListener('click', () => addExperienceItem());
    addEducationBtn.addEventListener('click', () => addEducationItem());

    salaryType.addEventListener('change', function() {
        const isAnnual = this.value === 'annual';
        const minLabel = document.querySelector('label[for="desired_salary_min"]');
        const maxLabel = document.querySelector('label[for="desired_salary_max"]');
        const minSuffix = document.getElementById('salary_suffix_min');
        const maxSuffix = document.getElementById('salary_suffix_max');

        if (isAnnual) {
            minLabel.textContent = 'Desired Salary (Minimum)';
            maxLabel.textContent = 'Desired Salary (Maximum)';
            minSuffix.textContent = '/yr';
            maxSuffix.textContent = '/yr';
        } else {
            minLabel.textContent = 'Desired Hourly Wage (Minimum)';
            maxLabel.textContent = 'Desired Hourly Wage (Maximum)';
            minSuffix.textContent = '/hr';
            maxSuffix.textContent = '/hr';
        }
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        let validationPassed = true;
        document.querySelectorAll('.experience-item').forEach(item => {
            item.querySelectorAll('input, textarea').forEach(input => {
                if (!input.value) {
                    validationPassed = false;
                }
            });
        });

        document.querySelectorAll('.education-item').forEach(item => {
            item.querySelectorAll('input').forEach(input => {
                if (!input.value) {
                    validationPassed = false;
                }
            });
        });

        if (!validationPassed) {
            alert('Please fill out all fields for experience and education entries.');
            return;
        }

        const formData = new FormData(form);
        const data = {
            headline: formData.get('headline'),
            summary: formData.get('summary'),
            location_city: formData.get('location_city'),
            location_province: formData.get('location_province'),
            is_remote: form.is_remote.checked,
            experience_years: parseInt(formData.get('experience_years')),
            availability: Array.from(document.querySelectorAll('input[name="availability[]"]:checked')).map(cb => cb.value),
            salary_type: formData.get('salary_type'),
            desired_salary_min: parseFloat(formData.get('desired_salary_min')),
            desired_salary_max: parseFloat(formData.get('desired_salary_max')),
            education_level: formData.get('education_level'),
            language_spoken: formData.get('language_spoken'),
            linkedin_url: formData.get('linkedin_url'),
            portfolio_url: formData.get('portfolio_url'),
            is_searchable: form.is_searchable.checked,
            skills: skills, // Send the whole skill object
            experience: [],
            education: []
        };

        const expTitles = formData.getAll('exp_job_title[]');
        for(let i = 0; i < expTitles.length; i++) {
            data.experience.push({
                job_title: expTitles[i],
                company: formData.getAll('exp_company[]')[i],
                start_date: formData.getAll('exp_start_date[]')[i],
                end_date: formData.getAll('exp_end_date[]')[i],
                description: formData.getAll('exp_description[]')[i]
            });
        }

        const eduSchools = formData.getAll('edu_school[]');
        for(let i = 0; i < eduSchools.length; i++) {
            data.education.push({
                school: eduSchools[i],
                degree: formData.getAll('edu_degree[]')[i],
                field_of_study: formData.getAll('edu_field_of_study[]')[i],
                start_year: formData.getAll('edu_start_year[]')[i],
                end_year: formData.getAll('edu_end_year[]')[i]
            });
        }

        try {
            const response = await fetch('api/update_candidate_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            alert(result.message);

        } catch (error) {
            console.error('Error updating profile:', error);
            alert('An error occurred. Please try again.');
        }
    });

    fetchProfile();
});