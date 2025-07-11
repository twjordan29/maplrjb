<?php
class Candidate
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProfile($user_id)
    {
        $profile = [];

        // Get main profile data
        $query = "SELECT * FROM candidates WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $profile = $result->fetch_assoc();
            $candidate_id = $profile['id'];

            // Get skills
            $profile['skills'] = $this->getSkills($candidate_id);

            // Get experience
            $profile['experience'] = $this->getExperience($candidate_id);

            // Get education
            $profile['education'] = $this->getEducation($candidate_id);
        }

        return $profile;
    }

    public function getSkills($candidate_id)
    {
        $skills = [];
        $query = "SELECT s.id, s.name FROM skills s JOIN candidate_skills cs ON s.id = cs.skill_id WHERE cs.candidate_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $skills[] = $row;
        }
        return $skills;
    }

    public function getExperience($candidate_id)
    {
        $experience = [];
        $query = "SELECT * FROM candidate_experiences WHERE candidate_id = ? ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $experience[] = $row;
        }
        return $experience;
    }

    public function getEducation($candidate_id)
    {
        $education = [];
        $query = "SELECT * FROM candidate_education WHERE candidate_id = ? ORDER BY start_year DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $education[] = $row;
        }
        return $education;
    }

    public function updateProfile($user_id, $data)
    {
        $this->conn->begin_transaction();

        try {
            // Get candidate ID
            $query_get_id = "SELECT id FROM candidates WHERE user_id = ?";
            $stmt_get_id = $this->conn->prepare($query_get_id);
            $stmt_get_id->bind_param("i", $user_id);
            $stmt_get_id->execute();
            $result_get_id = $stmt_get_id->get_result();
            if ($result_get_id->num_rows === 0) {
                // Create profile if it doesn't exist
                $this->createProfile($user_id);
                $stmt_get_id->execute();
                $result_get_id = $stmt_get_id->get_result();
            }
            $candidate_id = $result_get_id->fetch_assoc()['id'];


            // Update main profile
            $fields = [
                'headline' => 's',
                'summary' => 's',
                'location_city' => 's',
                'location_province' => 's',
                'is_remote' => 'i',
                'experience_years' => 'i',
                'availability' => 's',
                'salary_type' => 's',
                'desired_salary_min' => 'd',
                'desired_salary_max' => 'd',
                'education_level' => 's',
                'language_spoken' => 's',
                'linkedin_url' => 's',
                'portfolio_url' => 's',
                'is_searchable' => 'i',
                'latitude' => 'd',
                'longitude' => 'd'
            ];

            $query_parts = [];
            $params = [];
            $types = '';

            $data['availability'] = is_array($data['availability']) ? implode(',', $data['availability']) : ($data['availability'] ?? null);
            $data['language_spoken'] = $data['language_spoken'] ?? null;


            foreach ($fields as $field => $type) {
                if (isset($data[$field])) {
                    $query_parts[] = "$field = ?";
                    $params[] = &$data[$field];
                    $types .= $type;
                }
            }

            if (!empty($query_parts)) {
                $query_main = "UPDATE candidates SET " . implode(', ', $query_parts) . " WHERE id = ?";
                $params[] = &$candidate_id;
                $types .= 'i';

                $stmt_main = $this->conn->prepare($query_main);
                $stmt_main->bind_param($types, ...$params);
                $stmt_main->execute();
            }

            // Update skills
            $this->updateSkills($candidate_id, $data['skills']);

            // Update experience
            $this->updateExperience($candidate_id, $data['experience']);

            // Update education
            $this->updateEducation($candidate_id, $data['education']);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function createProfile($user_id)
    {
        $query = "INSERT INTO candidates (user_id) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function updateSkills($candidate_id, $skills)
    {
        // Clear existing skills
        $query_delete = "DELETE FROM candidate_skills WHERE candidate_id = ?";
        $stmt_delete = $this->conn->prepare($query_delete);
        $stmt_delete->bind_param("i", $candidate_id);
        $stmt_delete->execute();

        $query_insert = "INSERT INTO candidate_skills (candidate_id, skill_id) VALUES (?, ?)";
        $stmt_insert = $this->conn->prepare($query_insert);

        $query_find_skill = "SELECT id FROM skills WHERE name = ?";
        $stmt_find_skill = $this->conn->prepare($query_find_skill);

        $query_create_skill = "INSERT INTO skills (name) VALUES (?)";
        $stmt_create_skill = $this->conn->prepare($query_create_skill);

        foreach ($skills as $skill) {
            $skill_id = $skill['id'];
            if (is_null($skill_id)) {
                // Skill doesn't exist, create it
                $stmt_find_skill->bind_param("s", $skill['name']);
                $stmt_find_skill->execute();
                $result = $stmt_find_skill->get_result();
                if ($result->num_rows > 0) {
                    $skill_id = $result->fetch_assoc()['id'];
                } else {
                    $stmt_create_skill->bind_param("s", $skill['name']);
                    $stmt_create_skill->execute();
                    $skill_id = $stmt_create_skill->insert_id;
                }
            }

            if ($skill_id) {
                $stmt_insert->bind_param("ii", $candidate_id, $skill_id);
                $stmt_insert->execute();
            }
        }
    }

    public function updateExperience($candidate_id, $experiences)
    {
        // Clear existing experience
        $query_delete = "DELETE FROM candidate_experiences WHERE candidate_id = ?";
        $stmt_delete = $this->conn->prepare($query_delete);
        $stmt_delete->bind_param("i", $candidate_id);
        $stmt_delete->execute();

        // Add new experience
        $query_insert = "INSERT INTO candidate_experiences (candidate_id, job_title, company, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $this->conn->prepare($query_insert);
        foreach ($experiences as $exp) {
            $stmt_insert->bind_param("isssss", $candidate_id, $exp['job_title'], $exp['company'], $exp['start_date'], $exp['end_date'], $exp['description']);
            $stmt_insert->execute();
        }
    }

    public function updateEducation($candidate_id, $educations)
    {
        // Clear existing education
        $query_delete = "DELETE FROM candidate_education WHERE candidate_id = ?";
        $stmt_delete = $this->conn->prepare($query_delete);
        $stmt_delete->bind_param("i", $candidate_id);
        $stmt_delete->execute();

        // Add new education
        $query_insert = "INSERT INTO candidate_education (candidate_id, school, degree, field_of_study, start_year, end_year) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $this->conn->prepare($query_insert);
        foreach ($educations as $edu) {
            $stmt_insert->bind_param("isssii", $candidate_id, $edu['school'], $edu['degree'], $edu['field_of_study'], $edu['start_year'], $edu['end_year']);
            $stmt_insert->execute();
        }
    }

    public function getSkillsSuggestions($term)
    {
        $skills = [];
        if (!empty($term)) {
            $term = "%{$term}%";
            $query = "SELECT id, name, category FROM skills WHERE name LIKE ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $term);
        } else {
            $query = "SELECT id, name, category FROM skills ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $skills[] = $row;
        }
        return $skills;
    }
}
?>