package com.pin2fix.service;

import com.pin2fix.model.*;
import com.pin2fix.repository.*;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;
import java.util.Optional;

@Service
public class ApprovalService {
    private final HeadApprovalRepository headApprovalRepository;
    private final GovApprovalRepository govApprovalRepository;
    private final AssignmentRepository assignmentRepository;
    private final IssueRepository issueRepository;
    private final ActivityLogRepository activityLogRepository;
    private final NotificationRepository notificationRepository;
    private final UserRepository userRepository;

    public ApprovalService(HeadApprovalRepository headApprovalRepository, GovApprovalRepository govApprovalRepository,
                           AssignmentRepository assignmentRepository, IssueRepository issueRepository,
                           ActivityLogRepository activityLogRepository, NotificationRepository notificationRepository,
                           UserRepository userRepository) {
        this.headApprovalRepository = headApprovalRepository;
        this.govApprovalRepository = govApprovalRepository;
        this.assignmentRepository = assignmentRepository;
        this.issueRepository = issueRepository;
        this.activityLogRepository = activityLogRepository;
        this.notificationRepository = notificationRepository;
        this.userRepository = userRepository;
    }

    @Transactional
    public HeadApproval submitHeadApproval(Long assignmentId, Long approvedBy, ApprovalStatus status, String comment) {
        Assignment assignment = assignmentRepository.findById(assignmentId)
                .orElseThrow(() -> new RuntimeException("Assignment not found"));

        HeadApproval approval = HeadApproval.builder()
                .assignmentId(assignmentId)
                .approvedBy(approvedBy)
                .status(status)
                .comment(comment)
                .build();
        approval = headApprovalRepository.save(approval);

        Issue issue = issueRepository.findById(assignment.getIssueId())
                .orElseThrow(() -> new RuntimeException("Issue not found"));

        if (status == ApprovalStatus.APPROVED) {
            // Move to government body approval
            issue.setStatus(IssueStatus.HEAD_APPROVED);
            issueRepository.save(issue);

            // Notify government body users
            if (issue.getGovId() != null) {
                List<User> govUsers = userRepository.findByRoleAndGovId(Role.GOV_BODY, issue.getGovId());
                for (User govUser : govUsers) {
                    Notification notification = Notification.builder()
                            .userId(govUser.getUserId())
                            .issueId(issue.getIssueId())
                            .title("Work Pending Your Approval")
                            .message("Issue '" + issue.getTitle() + "' work has been approved by department head. Please review and confirm.")
                            .isRead(false)
                            .build();
                    notificationRepository.save(notification);
                }
            }
        } else {
            // Rejected - reopen for rework
            issue.setStatus(IssueStatus.REOPENED);
            issueRepository.save(issue);

            // Update assignment status
            assignment.setStatus(AssignmentStatus.REOPENED);
            assignmentRepository.save(assignment);

            // Notify worker
            if (assignment.getAssigneeId() != null) {
                Notification notification = Notification.builder()
                        .userId(assignment.getAssigneeId())
                        .issueId(issue.getIssueId())
                        .title("Work Rejected - Rework Required")
                        .message("Your work for issue '" + issue.getTitle() + "' has been rejected. Reason: " + comment)
                        .isRead(false)
                        .build();
                notificationRepository.save(notification);
            }
        }

        // Log activity
        ActivityLog log = ActivityLog.builder()
                .issueId(issue.getIssueId())
                .actorId(approvedBy)
                .action("HEAD_APPROVAL_" + status)
                .details("Head " + status.toString().toLowerCase() + " the work. Comment: " + comment)
                .build();
        activityLogRepository.save(log);

        return approval;
    }

    @Transactional
    public GovApproval submitGovApproval(Long issueId, Long govId, Long approvedBy, String comment) {
        Issue issue = issueRepository.findById(issueId)
                .orElseThrow(() -> new RuntimeException("Issue not found"));

        GovApproval approval = GovApproval.builder()
                .issueId(issueId)
                .govId(govId)
                .approvedBy(approvedBy)
                .comment(comment)
                .build();
        approval = govApprovalRepository.save(approval);

        // Mark issue as completed
        issue.setStatus(IssueStatus.COMPLETED);
        issueRepository.save(issue);

        // Notify the citizen
        if (issue.getReporterId() != null) {
            Notification notification = Notification.builder()
                    .userId(issue.getReporterId())
                    .issueId(issueId)
                    .title("Issue Resolved!")
                    .message("Your reported issue '" + issue.getTitle() + "' has been resolved. Please provide your feedback.")
                    .isRead(false)
                    .build();
            notificationRepository.save(notification);
        }

        // Log activity
        ActivityLog log = ActivityLog.builder()
                .issueId(issueId)
                .actorId(approvedBy)
                .action("GOV_APPROVAL")
                .details("Government body approved the work. Issue completed. Comment: " + comment)
                .build();
        activityLogRepository.save(log);

        return approval;
    }

    public List<HeadApproval> findHeadApprovalsByAssignmentId(Long assignmentId) {
        return headApprovalRepository.findByAssignmentId(assignmentId);
    }

    public Optional<HeadApproval> findLatestHeadApproval(Long assignmentId) {
        return headApprovalRepository.findTopByAssignmentIdOrderByApprovedAtDesc(assignmentId);
    }

    public List<GovApproval> findGovApprovalsByIssueId(Long issueId) {
        return govApprovalRepository.findByIssueId(issueId);
    }

    public Optional<GovApproval> findLatestGovApproval(Long issueId) {
        return govApprovalRepository.findTopByIssueIdOrderByApprovedAtDesc(issueId);
    }
}
