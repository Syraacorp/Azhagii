$replacements = @{
    'course_id' = 'courseId'
    'college_id' = 'collegeId'
    'created_at' = 'createdAt'
    'student_id' = 'studentId'
    'content_type' = 'contentType'
    'content_data' = 'contentData'
    'uploaded_by' = 'uploadedBy'
    'sort_order' = 'sortOrder'
    'course_code' = 'courseCode'
    'course_type' = 'courseType'
    'academic_year' = 'academicYear'
    'created_by' = 'createdBy'
    'approved_by' = 'approvedBy'
    'approved_at' = 'approvedAt'
    'rejection_reason' = 'rejectionReason'
    'updated_at' = 'updatedAt'
    'enrolled_at' = 'enrolledAt'
    'completed_at' = 'completedAt'
    'event_date' = 'eventDate'
    'event_time' = 'eventTime'
    'image_url' = 'imageUrl'
    'user_id' = 'userId'
    'request_reason' = 'requestReason'
    'resolved_at' = 'resolvedAt'
    'resolved_by' = 'resolvedBy'
    'subject_id' = 'subjectId'
    'roll_number' = 'rollNumber'
    'profile_photo' = 'profilePhoto'
    'github_url' = 'githubUrl'
    'linkedin_url' = 'linkedinUrl'
    'hackerrank_url' = 'hackerrankUrl'
    'leetcode_url' = 'leetcodeUrl'
    'is_locked' = 'isLocked'
    'assigned_by' = 'assignedBy'
    'assigned_at' = 'assignedAt'
    'enrollment_id' = 'enrollmentId'
    'college_name' = 'collegeName'
}

$files = Get-ChildItem -Path "D:\Software\Host\htdocs\Ziya" -Filter *.js -Recurse

$totalReplaced = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    $fileReplaced = 0
    
    foreach ($old in $replacements.Keys) {
        $new = $replacements[$old]
        $pattern = [regex]::Escape($old)
        $matches = [regex]::Matches($content, $pattern)
        $count = $matches.Count
        
        if ($count -gt 0) {
            $content = $content -replace $pattern, $new
            $fileReplaced += $count
        }
    }
    
    if ($fileReplaced -gt 0) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "$($file.Name): $fileReplaced replacements" -ForegroundColor Green
        $totalReplaced += $fileReplaced
    }
}

Write-Host "`nTotal replacements in JS files: $totalReplaced" -ForegroundColor Cyan
Write-Host "All JS files updated" -ForegroundColor Green
