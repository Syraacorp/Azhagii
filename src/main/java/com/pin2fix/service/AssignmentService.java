package com.pin2fix.service;

import com.pin2fix.model.*;
import com.pin2fix.repository.*;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;
import java.util.Optional;

@Service
public class AssignmentService {
    private final AssignmentRepository assignmentRepository;
    private final IssueRepository issueRepository;
    private final ActivityLogRepository activityLogRepository;
    private final NotificationRepository notificationRepository;

    public AssignmentService(AssignmentRepository assignmentRepository, IssueRepository issueRepository,
                             ActivityLogRepository activityLogRepository, NotificationRepository notificationRepository) {
        this.assignmentRepository = assignmentRepository;
        this.issueRepository = issueRepository;
        this.activityLogRepository = activityLogRepository;
        this.notificationRepository = notificationRepository;
    }

    @Transactional
    public Assignment createAssignment(Long issueId, Long assignedBy, Long assigneeId, Role roleAssignee, String comment) {
        Assignment assignment = Assignment.builder()
                .issueId(issueId)
                .assignedBy(assignedBy)
                .assigneeId(assigneeId)
                .roleAssignee(roleAssignee)
                .status(AssignmentStatus.ASSIGNED)
                .comment(comment)
                .build();
        
        assignment = assignmentRepository.save(assignment);

        // Update issue status
        Issue issue = issueRepository.findById(issueId)
                .orElseThrow(() -> new RuntimeException("Issue not found"));
        issue.setStatus(IssueStatus.ASSIGNED);
        issueRepository.save(issue);

        // Create notification for assignee
        Notification notification = Notification.builder()
                .userId(assigneeId)
                .issueId(issueId)
                .title("New Assignment")
                .message("You have been assigned a new task for issue: " + issue.getTitle())
                .isRead(false)
                .build();
        notificationRepository.save(notification);

        // Log activity
        ActivityLog log = ActivityLog.builder()
                .issueId(issueId)
                .actorId(assignedBy)
                .action("ASSIGNMENT_CREATED")
                .details("Task assigned to user ID: " + assigneeId + " as " + roleAssignee)
                .build();
        activityLogRepository.save(log);

        return assignment;
    }

    public Optional<Assignment> findById(Long id) {
        return assignmentRepository.findById(id);
    }

    public List<Assignment> findByIssueId(Long issueId) {
        return assignmentRepository.findByIssueId(issueId);
    }

    public List<Assignment> findByAssigneeId(Long assigneeId) {
        return assignmentRepository.findByAssigneeId(assigneeId);
    }

    public List<Assignment> findByAssigneeIdAndStatus(Long assigneeId, AssignmentStatus status) {
        return assignmentRepository.findByAssigneeIdAndStatus(assigneeId, status);
    }

    public Optional<Assignment> findLatestByIssueId(Long issueId) {
        return assignmentRepository.findTopByIssueIdOrderByAssignedAtDesc(issueId);
    }

    @Transactional
    public Assignment updateStatus(Long assignmentId, AssignmentStatus newStatus, Long actorId) {
        Assignment assignment = assignmentRepository.findById(assignmentId)
                .orElseThrow(() -> new RuntimeException("Assignment not found"));
        
        assignment.setStatus(newStatus);
        assignment = assignmentRepository.save(assignment);

        // Update issue status if work is in progress
        if (newStatus == AssignmentStatus.IN_PROGRESS) {
            Issue issue = issueRepository.findById(assignment.getIssueId())
                    .orElseThrow(() -> new RuntimeException("Issue not found"));
            issue.setStatus(IssueStatus.IN_PROGRESS);
            issueRepository.save(issue);
        }

        // Log activity
        ActivityLog log = ActivityLog.builder()
                .issueId(assignment.getIssueId())
                .actorId(actorId)
                .action("ASSIGNMENT_STATUS_UPDATED")
                .details("Assignment status updated to: " + newStatus)
                .build();
        activityLogRepository.save(log);

        return assignment;
    }

    public long countByAssigneeId(Long assigneeId) {
        return assignmentRepository.countByAssigneeId(assigneeId);
    }

    public long countByAssigneeIdAndStatus(Long assigneeId, AssignmentStatus status) {
        return assignmentRepository.countByAssigneeIdAndStatus(assigneeId, status);
    }
}
