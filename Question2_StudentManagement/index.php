<?php
/**
 * Admin Dashboard & Student Directory (index.php)
 * Displays summary metric cards, search & filter interface, and students records table.
 */
require_once __DIR__ . '/db.php';

$pageTitle = 'Dashboard';

// ----------------------------------------------------------------------------
// 1. Fetch Dashboard Metrics
// ----------------------------------------------------------------------------
try {
    // Total registered students
    $stmtTotalStudents = $pdo->query("SELECT COUNT(*) FROM students");
    $totalStudents = (int) $stmtTotalStudents->fetchColumn();

    // Total uploaded verification documents and files
    $stmtTotalFiles = $pdo->query("SELECT COUNT(*), COALESCE(SUM(file_size), 0) FROM student_files");
    $fileStats = $stmtTotalFiles->fetch(PDO::FETCH_NUM);
    $totalFiles = (int) $fileStats[0];
    $totalStorageBytes = (int) $fileStats[1];

    // Total distinct courses enrolled
    $stmtCourses = $pdo->query("SELECT COUNT(DISTINCT course) FROM students");
    $activeCourses = (int) $stmtCourses->fetchColumn();

} catch (PDOException $e) {
    setFlash('error', 'Error loading dashboard metrics: ' . $e->getMessage());
    $totalStudents = $totalFiles = $totalStorageBytes = $activeCourses = 0;
}

// ----------------------------------------------------------------------------
// 2. Handle Search & Filter Parameters (GET)
// ----------------------------------------------------------------------------
$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');

$sql = "SELECT s.id, s.name, s.roll_number, s.email, s.course, s.created_at,
               COUNT(f.id) AS file_count
        FROM students s
        LEFT JOIN student_files f ON s.id = f.student_id";

$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(s.name LIKE :search_name OR s.roll_number LIKE :search_roll OR s.email LIKE :search_email)";
    $params[':search_name'] = "%{$search}%";
    $params[':search_roll'] = "%{$search}%";
    $params[':search_email'] = "%{$search}%";
}

if ($courseFilter !== '' && in_array($courseFilter, ALLOWED_COURSES)) {
    $whereClauses[] = "s.course = :course";
    $params[':course'] = $courseFilter;
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " GROUP BY s.id, s.name, s.roll_number, s.email, s.course, s.created_at ORDER BY s.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlash('error', 'Error fetching student records: ' . $e->getMessage());
    $students = [];
}

require_once __DIR__ . '/header.php';
?>

<!-- Dashboard Metrics Row -->
<section class="dashboard-metrics" aria-label="Statistics Overview">
    <div class="metric-card">
        <div class="metric-icon metric-icon-blue">👥</div>
        <div class="metric-details">
            <span class="metric-label">Total Students</span>
            <strong class="metric-value"><?php echo number_format($totalStudents); ?></strong>
            <span class="metric-hint">Enrolled candidates</span>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon metric-icon-green">📁</div>
        <div class="metric-details">
            <span class="metric-label">Uploaded Documents</span>
            <strong class="metric-value"><?php echo number_format($totalFiles); ?></strong>
            <span class="metric-hint"><?php echo formatBytes($totalStorageBytes); ?> total storage</span>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon metric-icon-purple">🏛️</div>
        <div class="metric-details">
            <span class="metric-label">Active Branches</span>
            <strong class="metric-value"><?php echo number_format($activeCourses); ?></strong>
            <span class="metric-hint">Departments represented</span>
        </div>
    </div>

    <div class="metric-card metric-card-action">
        <div class="metric-action-content">
            <span class="metric-label">Quick Actions</span>
            <p class="metric-subtext">Register an applicant with documents</p>
            <a href="add_student.php" class="btn btn-primary btn-sm">
                <span>➕ Add New Student</span>
            </a>
        </div>
    </div>
</section>

<!-- Main Table Card -->
<section class="card table-card">
    <div class="card-header-bar">
        <div class="header-titles">
            <h2 class="section-title">Student Directory</h2>
            <p class="section-subtitle">Manage student enrollment records, profiles, and associated file uploads.</p>
        </div>
        <div class="header-tools">
            <a href="add_student.php" class="btn btn-primary">
                <span>➕ Add Student</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="filter-bar">
        <form method="GET" action="index.php" class="filter-form">
            <div class="search-input-wrap">
                <span class="search-icon" aria-hidden="true">🔍</span>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control search-input" 
                    placeholder="Search by name, roll no, or email..."
                    value="<?php echo e($search); ?>"
                >
            </div>

            <div class="select-filter-wrap">
                <select name="course" class="form-control select-input" onchange="this.form.submit()">
                    <option value="">All Engineering Branches</option>
                    <?php foreach (ALLOWED_COURSES as $course): ?>
                        <option value="<?php echo e($course); ?>" <?php echo ($courseFilter === $course) ? 'selected' : ''; ?>>
                            <?php echo e($course); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">Filter</button>

            <?php if ($search !== '' || $courseFilter !== ''): ?>
                <a href="index.php" class="btn btn-ghost" title="Clear filters">Clear</a>
            <?php endif; ?>
        </form>

        <div class="filter-summary">
            Showing <strong><?php echo count($students); ?></strong> student<?php echo count($students) === 1 ? '' : 's'; ?>
        </div>
    </div>

    <!-- Students Records Table -->
    <div class="table-responsive">
        <table class="data-table" aria-label="Students List">
            <thead>
                <tr>
                    <th scope="col" style="width: 60px;">#ID</th>
                    <th scope="col">Student Details</th>
                    <th scope="col">Roll Number</th>
                    <th scope="col">Branch / Course</th>
                    <th scope="col" style="text-align: center;">Files</th>
                    <th scope="col">Registered</th>
                    <th scope="col" style="text-align: right; width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="7" class="empty-table-cell">
                            <div class="empty-state">
                                <span class="empty-icon">📂</span>
                                <h3>No Students Found</h3>
                                <p><?php echo ($search !== '' || $courseFilter !== '') ? 'No student records matched your search query or filter.' : 'No students have been registered yet.'; ?></p>
                                <?php if ($search !== '' || $courseFilter !== ''): ?>
                                    <a href="index.php" class="btn btn-secondary btn-sm">Reset Search Filters</a>
                                <?php else: ?>
                                    <a href="add_student.php" class="btn btn-primary btn-sm">Add First Student</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <?php 
                            // Compute student initials for avatar icon
                            $initials = '';
                            $nameParts = explode(' ', trim($student['name']));
                            foreach ($nameParts as $part) {
                                if (!empty($part)) $initials .= strtoupper($part[0]);
                                if (strlen($initials) >= 2) break;
                            }
                        ?>
                        <tr>
                            <td class="cell-id">#<?php echo e($student['id']); ?></td>
                            <td class="cell-student">
                                <div class="student-avatar" aria-hidden="true">
                                    <?php echo e($initials ?: 'ST'); ?>
                                </div>
                                <div class="student-meta">
                                    <a href="view_student.php?id=<?php echo e($student['id']); ?>" class="student-name-link">
                                        <strong><?php echo e($student['name']); ?></strong>
                                    </a>
                                    <span class="student-email"><?php echo e($student['email']); ?></span>
                                </div>
                            </td>
                            <td class="cell-roll">
                                <span class="roll-badge"><?php echo e($student['roll_number']); ?></span>
                            </td>
                            <td class="cell-course">
                                <span class="course-badge course-badge-<?php echo strtolower(e($student['course'])); ?>">
                                    <?php echo e($student['course']); ?>
                                </span>
                            </td>
                            <td class="cell-files" style="text-align: center;">
                                <?php if ($student['file_count'] > 0): ?>
                                    <a href="view_student.php?id=<?php echo e($student['id']); ?>#documents" class="file-count-badge has-files" title="<?php echo e($student['file_count']); ?> uploaded files">
                                        📎 <?php echo e($student['file_count']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="file-count-badge no-files" title="No files uploaded">
                                        0
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="cell-date">
                                <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
                            </td>
                            <td class="cell-actions" style="text-align: right;">
                                <div class="action-btn-group">
                                    <a href="view_student.php?id=<?php echo e($student['id']); ?>" class="btn-action btn-action-view" title="View Student Profile">
                                        👁️ View
                                    </a>
                                    <a href="edit_student.php?id=<?php echo e($student['id']); ?>" class="btn-action btn-action-edit" title="Edit Student">
                                        ✏️ Edit
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn-action btn-action-delete" 
                                        onclick="confirmDelete(<?php echo (int) $student['id']; ?>, '<?php echo addslashes(e($student['name'])); ?>', '<?php echo addslashes(e($student['roll_number'])); ?>')"
                                        title="Delete Student"
                                    >
                                        🗑️ Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
