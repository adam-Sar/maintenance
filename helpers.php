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
