package com.pin2fix.service;

import com.pin2fix.model.*;
import com.pin2fix.repository.*;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;
import java.util.Optional;

@Service
public class FeedbackService {
    private final FeedbackRepository feedbackRepository;
    private final IssueRepository issueRepository;
    private final AssignmentRepository assignmentRepository;
    private final ActivityLogRepository activityLogRepository;
    private final NotificationRepository notificationRepository;

    public FeedbackService(FeedbackRepository feedbackRepository, IssueRepository issueRepository,
                           AssignmentRepository assignmentRepository, ActivityLogRepository activityLogRepository,
                           NotificationRepository notificationRepository) {
        this.feedbackRepository = feedbackRepository;
        this.issueRepository = issueRepository;
        this.assignmentRepository = assignmentRepository;
        this.activityLogRepository = activityLogRepository;
        this.notificationRepository = notificationRepository;
    }

    @Transactional
    public Feedback submitFeedback(Long issueId, Long userId, Integer rating, String message, Boolean isPositive) {
        Issue issue = issueRepository.findById(issueId)
                .orElseThrow(() -> new RuntimeException("Issue not found"));

        // Check if user already submitted feedback
        if (feedbackRepository.existsByIssueIdAndUserId(issueId, userId)) {
            throw new RuntimeException("Feedback already submitted for this issue");
        }

        Feedback feedback = Feedback.builder()
                .issueId(issueId)
                .userId(userId)
                .rating(rating)
                .message(message)
                .isPositive(isPositive)
                .build();
        feedback = feedbackRepository.save(feedback);

        if (!isPositive) {
            // Negative feedback - reopen the issue for rework
            issue.setStatus(IssueStatus.REOPENED);
            issueRepository.save(issue);

            // Reopen the latest assignment
            Optional<Assignment> latestAssignment = assignmentRepository.findTopByIssueIdOrderByAssignedAtDesc(issueId);
            if (latestAssignment.isPresent()) {
                Assignment assignment = latestAssignment.get();
                assignment.setStatus(AssignmentStatus.REOPENED);
                assignmentRepository.save(assignment);

                // Notify the worker
                if (assignment.getAssigneeId() != null) {
                    Notification notification = Notification.builder()
                            .userId(assignment.getAssigneeId())
                            .issueId(issueId)
                            .title("Negative Feedback - Rework Required")
                            .message("The citizen has provided negative feedback for issue '" + issue.getTitle() + "'. Rework is required.")
                            .isRead(false)
                            .build();
                    notificationRepository.save(notification);
                }

                // Notify the department head
                if (assignment.getAssignedBy() != null) {
                    Notification notification = Notification.builder()
                            .userId(assignment.getAssignedBy())
                            .issueId(issueId)
                            .title("Negative Feedback Received")
                            .message("Citizen provided negative feedback for issue '" + issue.getTitle() + "'. Issue has been reopened.")
                            .isRead(false)
                            .build();
                    notificationRepository.save(notification);
                }
            }

            // Log activity
            ActivityLog log = ActivityLog.builder()
                    .issueId(issueId)
                    .actorId(userId)
                    .action("NEGATIVE_FEEDBACK")
                    .details("Citizen provided negative feedback. Issue reopened for rework. Message: " + message)
                    .build();
            activityLogRepository.save(log);
        } else {
            // Positive feedback - issue remains completed
            ActivityLog log = ActivityLog.builder()
                    .issueId(issueId)
                    .actorId(userId)
                    .action("POSITIVE_FEEDBACK")
                    .details("Citizen provided positive feedback. Rating: " + rating + ". Message: " + message)
                    .build();
            activityLogRepository.save(log);
        }

        return feedback;
    }

    public List<Feedback> findByIssueId(Long issueId) {
        return feedbackRepository.findByIssueId(issueId);
    }

    public List<Feedback> findByUserId(Long userId) {
        return feedbackRepository.findByUserId(userId);
    }

    public Optional<Feedback> findByIssueIdAndUserId(Long issueId, Long userId) {
        return feedbackRepository.findByIssueIdAndUserId(issueId, userId);
    }

    public boolean hasFeedback(Long issueId, Long userId) {
        return feedbackRepository.existsByIssueIdAndUserId(issueId, userId);
    }

    public List<Feedback> findAll() {
        return feedbackRepository.findAll();
    }
}
