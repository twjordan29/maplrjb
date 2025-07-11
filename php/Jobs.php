<?php
class Jobs
{
    private $conn;
    private $table_name = "jobs";

    // Job Properties
    public $id;
    public $employer_id;
    public $title;
    public $description;
    public $job_type;
    public $salary_type;
    public $annual_salary_min;
    public $annual_salary_max;
    public $hourly_rate_min;
    public $hourly_rate_max;
    public $location;
    public $province;
    public $city;
    public $created_at;
    public $is_active;

    // Properties from assumed 'employers' table
    public $company_name;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getAllJobs($page = 1, $perPage = 10, $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        // Base query components
        $select_fields = "j.id, j.employer_id, j.title, j.description, j.job_type, j.salary_type, j.annual_salary_min, j.annual_salary_max, j.hourly_rate_min, j.hourly_rate_max, j.city, j.province, j.created_at, e.company_name, e.plan_type, e.logo, e.header_image, (e.plan_type = 'premium' OR e.plan_type = 'verified') as is_premium";
        $where_clauses = ['j.is_active = 1'];
        $having_clauses = [];
        $order_by = "j.created_at DESC";

        // Parameter arrays in order of appearance in the query
        $all_params = [];
        $all_types = '';

        // Location filter
        if (!empty($filters['location'])) {
            require_once 'Geocoding.php';
            $coords = Geocoding::getCoordinates($filters['location']);
            if ($coords) {
                $lat = $coords['latitude'];
                $lon = $coords['longitude'];
                $radius = $filters['radius'] ?? 50; // Default radius 50km

                $select_fields .= ", ( 6371 * acos( cos( radians(?) ) * cos( radians( j.latitude ) ) * cos( radians( j.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( j.latitude ) ) ) ) AS distance";
                $all_params = array_merge($all_params, [$lat, $lon, $lat]);
                $all_types .= 'ddd';

                $having_clauses[] = "distance < ?";
                // This param will be added later, after the WHERE params

                $order_by = "distance ASC, j.created_at DESC";
            } else {
                // If geocoding fails, ensure no results are returned for the location search
                $where_clauses[] = '1 = 0'; // This will always be false
            }
        }

        // Other filters
        if (!empty($filters['job_type'])) {
            $where_clauses[] = 'j.job_type = ?';
            $all_params[] = $filters['job_type'];
            $all_types .= 's';
        }
        if (!empty($filters['salary_type'])) {
            $where_clauses[] = 'j.salary_type = ?';
            $all_params[] = $filters['salary_type'];
            $all_types .= 's';
        }
        if (!empty($filters['salary'])) {
            $salary_val = (int) $filters['salary'];
            $hourly_equivalent = $salary_val / 2080;
            $where_clauses[] = '((j.salary_type = "salary" AND (j.annual_salary_min >= ? OR j.annual_salary_max >= ?)) OR (j.salary_type = "hourly" AND (j.hourly_rate_min >= ? OR j.hourly_rate_max >= ?)))';
            array_push($all_params, $salary_val, $salary_val, $hourly_equivalent, $hourly_equivalent);
            $all_types .= 'dddd';
        }
        if (!empty($filters['keyword'])) {
            $where_clauses[] = '(j.title LIKE ? OR e.company_name LIKE ?)';
            $keyword_param = '%' . $filters['keyword'] . '%';
            array_push($all_params, $keyword_param, $keyword_param);
            $all_types .= 'ss';
        }

        // Add the having param now that all where params are set
        if (!empty($having_clauses)) {
            $all_params[] = $radius;
            $all_types .= 'i';
        }

        // Build final SQL parts
        $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
        $having_sql = count($having_clauses) > 0 ? 'HAVING ' . implode(' AND ', $having_clauses) : '';

        // Query to get the total number of jobs
        $count_query = "SELECT COUNT(*) as total FROM (SELECT " . $select_fields . " FROM " . $this->table_name . " j LEFT JOIN employers e ON j.employer_id = e.id " . $where_sql . " " . $having_sql . ") as subquery";
        $count_stmt = $this->conn->prepare($count_query);

        if ($count_stmt === false) {
            die('SQL prepare error for count: ' . htmlspecialchars($this->conn->error));
        }

        if (!empty($all_params)) {
            $count_stmt->bind_param($all_types, ...$all_params);
        }
        $count_stmt->execute();
        $total_jobs = $count_stmt->get_result()->fetch_assoc()['total'];

        // Query to get the paginated jobs
        $query = "SELECT " . $select_fields . "
                FROM " . $this->table_name . " j
                LEFT JOIN employers e ON j.employer_id = e.id
                " . $where_sql . "
                " . $having_sql . "
                ORDER BY " . (isset($filters['location']) && $coords ? "distance ASC, " : "") . "j.created_at DESC
                LIMIT ? OFFSET ?";

        $final_params = array_merge($all_params, [$perPage, $offset]);
        $final_types = $all_types . 'ii';

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die('SQL prepare error for select: ' . htmlspecialchars($this->conn->error));
        }

        if (!empty($final_params)) {
            $stmt->bind_param($final_types, ...$final_params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        return [
            'jobs' => $result,
            'total' => $total_jobs,
            'pages' => ceil($total_jobs / $perPage)
        ];
    }

    public function createJobListing($employer_id, $title, $description, $job_type, $salary_type, $annual_salary_min, $annual_salary_max, $hourly_rate_min, $hourly_rate_max, $location, $city, $province, $expires_at, $questions, $is_recurring, $recurrence_interval)
    {
        require_once 'Geocoding.php';
        $coords = Geocoding::getCoordinates($location . ", " . $city . ", " . $province);
        $latitude = $coords['latitude'] ?? null;
        $longitude = $coords['longitude'] ?? null;

        $next_recurrence_date = null;
        if ($is_recurring) {
            if ($recurrence_interval === 'weekly') {
                $next_recurrence_date = date('Y-m-d', strtotime('+1 week'));
            } elseif ($recurrence_interval === 'monthly') {
                $next_recurrence_date = date('Y-m-d', strtotime('+1 month'));
            }
        }

        $query = "INSERT INTO " . $this->table_name . " (employer_id, title, description, job_type, salary_type, annual_salary_min, annual_salary_max, hourly_rate_min, hourly_rate_max, location, city, province, latitude, longitude, expires_at, is_recurring, recurrence_interval, next_recurrence_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        $is_recurring_int = $is_recurring ? 1 : 0;
        $stmt->bind_param("issssddddsssddssis", $employer_id, $title, $description, $job_type, $salary_type, $annual_salary_min, $annual_salary_max, $hourly_rate_min, $hourly_rate_max, $location, $city, $province, $latitude, $longitude, $expires_at, $is_recurring_int, $recurrence_interval, $next_recurrence_date);

        if ($stmt->execute()) {
            $job_id = $this->conn->insert_id;
            if (isset($questions) && is_array($questions)) {
                if (!$this->saveQuestions($job_id, $questions)) {
                    // You might want to log an error here
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    function getJobById($job_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function updateJob($id, $title, $description, $job_type, $salary_type, $annual_salary_min, $annual_salary_max, $hourly_rate_min, $hourly_rate_max, $location, $city, $province, $expires_at, $questions, $is_recurring, $recurrence_interval)
    {
        require_once 'Geocoding.php';
        $coords = Geocoding::getCoordinates($location . ", " . $city . ", " . $province);
        $latitude = $coords['latitude'] ?? null;
        $longitude = $coords['longitude'] ?? null;

        $next_recurrence_date = null;
        if ($is_recurring) {
            if ($recurrence_interval === 'weekly') {
                $next_recurrence_date = date('Y-m-d', strtotime('+1 week'));
            } elseif ($recurrence_interval === 'monthly') {
                $next_recurrence_date = date('Y-m-d', strtotime('+1 month'));
            }
        }

        $query = "UPDATE " . $this->table_name . " SET
                    title = ?,
                    description = ?,
                    job_type = ?,
                    salary_type = ?,
                    annual_salary_min = ?,
                    annual_salary_max = ?,
                    hourly_rate_min = ?,
                    hourly_rate_max = ?,
                    location = ?,
                    city = ?,
                    province = ?,
                    latitude = ?,
                    longitude = ?,
                    expires_at = ?,
                    is_recurring = ?,
                    recurrence_interval = ?,
                    next_recurrence_date = ?
                WHERE
                    id = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("ssssddddsssddsisssi", $title, $description, $job_type, $salary_type, $annual_salary_min, $annual_salary_max, $hourly_rate_min, $hourly_rate_max, $location, $city, $province, $latitude, $longitude, $expires_at, $is_recurring, $recurrence_interval, $next_recurrence_date, $id);

        if ($stmt->execute()) {
            if (isset($questions) && is_array($questions)) {
                $this->saveQuestions($id, $questions);
            }
            return true;
        }

        return false;
    }

    function getJobsByEmployer($employer_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE employer_id = ? ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $employer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    public function saveQuestions($job_id, $questions)
    {
        file_put_contents('save_questions_debug.log', "Job ID: $job_id\n", FILE_APPEND);
        file_put_contents('save_questions_debug.log', print_r($questions, true), FILE_APPEND);
        // First, delete existing questions for this job to handle updates
        $delete_stmt = $this->conn->prepare("DELETE FROM job_questions WHERE job_id = ?");
        $delete_stmt->bind_param("i", $job_id);
        $delete_stmt->execute();

        // Now, insert the new questions
        $stmt = $this->conn->prepare("INSERT INTO job_questions (job_id, question_text) VALUES (?, ?)");
        foreach ($questions as $question) {
            $stmt->bind_param("is", $job_id, $question);
            if (!$stmt->execute()) {
                // You might want to log an error here
                return false;
            }
        }
        return true;
    }

    public function getQuestionsByJobId($job_id)
    {
        $query = "SELECT * FROM job_questions WHERE job_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createApplication($job_id, $user_id, $name, $email, $cover_letter, $resume_path, $answers = [])
    {
        $query = "INSERT INTO applications (job_id, user_id, name, email, cover_letter, resume_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iissss", $job_id, $user_id, $name, $email, $cover_letter, $resume_path);

        if ($stmt->execute()) {
            $application_id = $this->conn->insert_id;
            if (!empty($answers)) {
                $answer_stmt = $this->conn->prepare("INSERT INTO job_application_answers (application_id, question_id, answer_text) VALUES (?, ?, ?)");
                foreach ($answers as $question_id => $answer_text) {
                    $answer_stmt->bind_param("iis", $application_id, $question_id, $answer_text);
                    if (!$answer_stmt->execute()) {
                        // Log error
                        error_log("Failed to save answer for question $question_id: " . $answer_stmt->error);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getApplicantCount($job_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM applications WHERE job_id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    public function getScreeningAnswers($application_id)
    {
        $query = "SELECT jq.question_text, aa.answer_text
                  FROM job_application_answers aa
                  JOIN job_questions jq ON aa.question_id = jq.id
                  WHERE aa.application_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getApplicationsByUserId($user_id)
    {
        $query = "SELECT
                    a.id as application_id,
                    a.applied_at as application_date,
                    a.status,
                    j.title as job_title,
                    e.company_name,
                    i.interview_datetime as interview_date
                  FROM applications a
                  JOIN jobs j ON a.job_id = j.id
                  LEFT JOIN employers e ON j.employer_id = e.id
                  LEFT JOIN interviews i ON a.id = i.application_id
                  WHERE a.user_id = ?
                  ORDER BY a.applied_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getInterviewsByEmployerId($employer_id)
    {
        $query = "SELECT
                    i.id as interview_id,
                    i.interview_datetime,
                    i.status,
                    j.title as job_title,
                    u.first_name,
                    u.last_name
                  FROM interviews i
                  JOIN applications a ON i.application_id = a.id
                  JOIN jobs j ON a.job_id = j.id
                  JOIN users u ON a.user_id = u.id
                  WHERE j.employer_id = ?
                  ORDER BY i.interview_datetime DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $employer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $interviews = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($interviews as $key => $interview) {
            $interviews[$key]['applicant_name'] = $interview['first_name'] . ' ' . $interview['last_name'];
        }

        return $interviews;
    }

    public function getSearchSuggestions($term)
    {
        $suggestions = [];
        $term = "%{$term}%";

        // Search for job titles
        $query_jobs = "SELECT DISTINCT title FROM " . $this->table_name . " WHERE title LIKE ? AND is_active = 1 LIMIT 5";
        $stmt_jobs = $this->conn->prepare($query_jobs);
        $stmt_jobs->bind_param("s", $term);
        $stmt_jobs->execute();
        $result_jobs = $stmt_jobs->get_result();
        while ($row = $result_jobs->fetch_assoc()) {
            $suggestions[] = ['value' => $row['title'], 'type' => 'Job'];
        }

        // Search for company names
        $query_companies = "SELECT DISTINCT e.company_name FROM employers e JOIN " . $this->table_name . " j ON e.id = j.employer_id WHERE e.company_name LIKE ? AND j.is_active = 1 LIMIT 5";
        $stmt_companies = $this->conn->prepare($query_companies);
        $stmt_companies->bind_param("s", $term);
        $stmt_companies->execute();
        $result_companies = $stmt_companies->get_result();
        while ($row = $result_companies->fetch_assoc()) {
            $suggestions[] = ['value' => $row['company_name'], 'type' => 'Company'];
        }

        return $suggestions;
    }
}
?>