<?php
// Helper functions for data management

function getJsonData($filename) {
    $filepath = __DIR__ . '/data/' . $filename;
    if (!file_exists($filepath)) {
        return [];
    }
    $data = file_get_contents($filepath);
    return json_decode($data, true) ?? [];
}

function saveJsonData($filename, $data) {
    $filepath = __DIR__ . '/data/' . $filename;
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
}

function getUserByEmail($email) {
    $users = getJsonData('users.json');
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

function getOrganizationById($id) {
    $organizations = getJsonData('organizations.json');
    foreach ($organizations as $org) {
        if ($org['id'] == $id) {
            return $org;
        }
    }
    return null;
}

function getComplaintsByOrganization($organizationId) {
    $complaints = getJsonData('complaints.json');
    return array_filter($complaints, function($complaint) use ($organizationId) {
        return $complaint['organization_id'] == $organizationId;
    });
}

function getComplaintsByUser($userId) {
    $complaints = getJsonData('complaints.json');
    return array_filter($complaints, function($complaint) use ($userId) {
        return $complaint['user_id'] == $userId;
    });
}

function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'high':
            return 'priority-high';
        case 'medium':
            return 'priority-medium';
        case 'low':
            return 'priority-low';
        default:
            return 'priority-medium';
    }
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'status-pending';
        case 'in_progress':
            return 'status-progress';
        case 'resolved':
            return 'status-resolved';
        default:
            return 'status-pending';
    }
}

function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

function getNextId($filename) {
    $data = getJsonData($filename);
    if (empty($data)) {
        return 1;
    }
    $maxId = max(array_column($data, 'id'));
    return $maxId + 1;
}

function getDepartmentsByOrganization($organizationId) {
    $departments = getJsonData('departments.json');
    return array_filter($departments, function($dept) use ($organizationId) {
        return $dept['organization_id'] == $organizationId;
    });
}

function getUserDepartments($userId, $organizationId = null) {
    $userDepts = getJsonData('user_departments.json');
    $filtered = array_filter($userDepts, function($ud) use ($userId, $organizationId) {
        if ($organizationId) {
            return $ud['user_id'] == $userId && $ud['organization_id'] == $organizationId;
        }
        return $ud['user_id'] == $userId;
    });
    return array_values($filtered);
}

function getUserOrganizations($userId) {
    $userDepts = getUserDepartments($userId);
    $orgIds = array_unique(array_column($userDepts, 'organization_id'));
    
    $organizations = getJsonData('organizations.json');
    return array_filter($organizations, function($org) use ($orgIds) {
        return in_array($org['id'], $orgIds);
    });
}

function getDepartmentById($departmentId) {
    $departments = getJsonData('departments.json');
    foreach ($departments as $dept) {
        if ($dept['id'] == $departmentId) {
            return $dept;
        }
    }
    return null;
}

function getComplaintsByDepartment($departmentId, $userId = null) {
    $complaints = getJsonData('complaints.json');
    return array_filter($complaints, function($complaint) use ($departmentId, $userId) {
        $deptMatch = isset($complaint['department_id']) && $complaint['department_id'] == $departmentId;
        if ($userId) {
            return $deptMatch && $complaint['user_id'] == $userId;
        }
        return $deptMatch;
    });
}

function isUserInDepartment($userId, $departmentId) {
    $userDepts = getJsonData('user_departments.json');
    foreach ($userDepts as $ud) {
        if ($ud['user_id'] == $userId && $ud['department_id'] == $departmentId) {
            return true;
        }
    }
    return false;
}
