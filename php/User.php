<?php
class User
{
    private $conn;
    private $table = 'users';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Check if email exists
    public function emailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT id FROM " . $this->table . " WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Create a new user
    public function create($data)
    {
        // Hash the password for security
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . " (first_name, last_name, email, password, user_type) VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $password_hash,
            $data['user_type']
        );

        return $stmt->execute();
    }

    // Get user by ID
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, user_type, phone, resume_path, profile_picture, city, province, date_of_birth, education_level FROM " . $this->table . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update user - Note: This would also need updating to hash passwords if used.
    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $data['name'], $data['email'], $data['password'], $id);
        return $stmt->execute();
    }

    // Delete user
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Login user
    public function login($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, password, user_type FROM " . $this->table . " WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function checkIfEmployerProfileComplete($userId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM employers WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $employer = $result->fetch_assoc();
            // Check if all required fields are filled
            return !empty($employer['company_name']) && !empty($employer['website']) && !empty($employer['description'])
                && !empty($employer['location']) && !empty($employer['province']) && !empty($employer['city']);
        }
        return false;
    }
    public function getEmployerProfile($userId)
    {
        $stmt = $this->conn->prepare("SELECT user_id, company_name, website, description, location, province, city, plan_type, logo, header_image, video_url, social_links, brand_color, work_for_us_desc FROM employers WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEmployerIdByCompanyName($companyName)
    {
        $stmt = $this->conn->prepare("SELECT user_id FROM employers WHERE company_name = ?");
        $stmt->bind_param("s", $companyName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['user_id'];
        }
        return null;
    }

    public function saveEmployerProfile($userId, $data, $files)
    {
        $profile = $this->getEmployerProfile($userId);

        // Handle file uploads
        $logoPath = $profile['logo'] ?? null;
        if (isset($files['logo']) && $files['logo']['error'] == 0) {
            $targetDir = "../uploads/logos/";
            if (!is_dir($targetDir))
                mkdir($targetDir, 0755, true);
            $fileName = uniqid() . '-' . basename($files['logo']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($files['logo']['tmp_name'], $targetFile)) {
                $logoPath = "uploads/logos/" . $fileName;
            }
        }

        $headerPath = $profile['header_image'] ?? null;
        if (isset($files['header']) && $files['header']['error'] == 0) {
            $targetDir = "../uploads/headers/";
            if (!is_dir($targetDir))
                mkdir($targetDir, 0755, true);
            $fileName = uniqid() . '-' . basename($files['header']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($files['header']['tmp_name'], $targetFile)) {
                $headerPath = "uploads/headers/" . $fileName;
            }
        }

        // Check if a profile already exists
        $stmt = $this->conn->prepare("SELECT id FROM employers WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->store_result();
        $profileExists = $stmt->num_rows > 0;
        $stmt->close();

        if ($profileExists) {
            // Update existing profile
            $stmt = $this->conn->prepare(
                "UPDATE employers SET company_name = ?, website = ?, description = ?, location = ?, province = ?, city = ?, logo = ?, header_image = ?, video_url = ?, social_links = ?, brand_color = ?, work_for_us_desc = ? WHERE user_id = ?"
            );
            $social_links_json = json_encode($data['social_links']);
            $stmt->bind_param(
                "ssssssssssssi",
                $data['company_name'],
                $data['website'],
                $data['description'],
                $data['location'],
                $data['province'],
                $data['city'],
                $logoPath,
                $headerPath,
                $data['video_url'],
                $social_links_json,
                $data['brand_color'],
                $data['work_for_us_desc'],
                $userId
            );
        } else {
            // Insert new profile
            $stmt = $this->conn->prepare(
                "INSERT INTO employers (user_id, company_name, website, description, location, province, city, logo, header_image, video_url, social_links, brand_color, work_for_us_desc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $social_links_json = json_encode($data['social_links']);
            $stmt->bind_param(
                "issssssssssss",
                $userId,
                $data['company_name'],
                $data['website'],
                $data['description'],
                $data['location'],
                $data['province'],
                $data['city'],
                $logoPath,
                $headerPath,
                $data['video_url'],
                $social_links_json,
                $data['brand_color'],
                $data['work_for_us_desc']
            );
        }

        return $stmt->execute();
    }

    public function updateJobSeekerProfile($userId, $data, $files)
    {
        $user = $this->getById($userId);
        $resumePath = $user['resume_path'] ?? null;
        $profilePicturePath = $user['profile_picture'] ?? null;

        if (isset($files['resume']) && $files['resume']['error'] == 0) {
            $targetDir = "../uploads/resumes/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileName = uniqid() . '-' . basename($files['resume']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($files['resume']['tmp_name'], $targetFile)) {
                $resumePath = "uploads/resumes/" . $fileName;
            }
        }

        if (isset($files['profile_picture']) && $files['profile_picture']['error'] == 0) {
            $targetDir = "../uploads/profile_pictures/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileName = uniqid() . '-' . basename($files['profile_picture']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($files['profile_picture']['tmp_name'], $targetFile)) {
                $profilePicturePath = "uploads/profile_pictures/" . $fileName;
            }
        }

        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " SET first_name = ?, last_name = ?, email = ?, phone = ?, city = ?, province = ?, date_of_birth = ?, education_level = ?, resume_path = ?, profile_picture = ? WHERE id = ?"
        );

        $stmt->bind_param(
            "ssssssssssi",
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['city'],
            $data['province'],
            $data['date_of_birth'],
            $data['education_level'],
            $resumePath,
            $profilePicturePath,
            $userId
        );

        return $stmt->execute();
    }

    public function saveJob($userId, $jobId)
    {
        $stmt = $this->conn->prepare("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
        if (!$stmt) {
            error_log("Prepare failed in saveJob: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $userId, $jobId);
        return $stmt->execute();
    }

    public function unsaveJob($userId, $jobId)
    {
        $stmt = $this->conn->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ii", $userId, $jobId);
        return $stmt->execute();
    }
    public function getSavedJobIds($userId)
    {
        $stmt = $this->conn->prepare("SELECT job_id FROM saved_jobs WHERE user_id = ?");
        if (!$stmt) {
            // Log or display the error for debugging
            error_log("Prepare failed: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $saved_jobs = [];
        while ($row = $result->fetch_assoc()) {
            $saved_jobs[] = $row['job_id'];
        }
        return $saved_jobs;
    }
}