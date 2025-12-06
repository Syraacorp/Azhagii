package com.pin2fix.service;

import com.pin2fix.model.*;
import com.pin2fix.repository.*;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
public class WorkEvidenceService {
    private final WorkEvidenceRepository workEvidenceRepository;
    private final AssignmentRepository assignmentRepository;
    private final IssueRepository issueRepository;
    private final ActivityLogRepository activityLogRepository;
    private final NotificationRepository notificationRepository;
    private final UserRepository userRepository;

    public WorkEvidenceService(WorkEvidenceRepository workEvidenceRepository, AssignmentRepository assignmentRepository,
                               IssueRepository issueRepository, ActivityLogRepository activityLogRepository,
                               NotificationRepository notificationRepository, UserRepository userRepository) {
        this.workEvidenceRepository = workEvidenceRepository;
        this.assignmentRepository = assignmentRepository;
        this.issueRepository = issueRepository;
        this.activityLogRepository = activityLogRepository;
        this.notificationRepository = notificationRepository;
        this.userRepository = userRepository;
    }

    @Transactional
    public WorkEvidence submitEvidence(Long assignmentId, Long workerId, String url, String notes) {
        Assignment assignment = assignmentRepository.findById(assignmentId)
                .orElseThrow(() -> new RuntimeException("Assignment not found"));

        WorkEvidence evidence = WorkEvidence.builder()
                .assignmentId(assignmentId)
                .workerId(workerId)
                .url(url)
                .notes(notes)
                .build();
        evidence = workEvidenceRepository.save(evidence);

        // Update assignment status
        assignment.setStatus(AssignmentStatus.COMPLETED);
        assignmentRepository.save(assignment);

        // Update issue status
        Issue issue = issueRepository.findById(assignment.getIssueId())
                .orElseThrow(() -> new RuntimeException("Issue not found"));
        issue.setStatus(IssueStatus.WORK_COMPLETED_PENDING_HEAD_APPROVAL);
        issueRepository.save(issue);

        // Notify the department head or area head
        if (assignment.getAssignedBy() != null) {
            Notification notification = Notification.builder()
                    .userId(assignment.getAssignedBy())
                    .issueId(assignment.getIssueId())
                    .title("Work Evidence Submitted")
                    .message("Worker has submitted evidence for issue: " + issue.getTitle() + ". Please review and approve.")
                    .isRead(false)
                    .build();
            notificationRepository.save(notification);
        }

        // Log activity
        ActivityLog log = ActivityLog.builder()
                .issueId(assignment.getIssueId())
                .actorId(workerId)
                .action("EVIDENCE_SUBMITTED")
                .details("Work evidence submitted for review")
                .build();
        activityLogRepository.save(log);

        return evidence;
    }

    public List<WorkEvidence> findByAssignmentId(Long assignmentId) {
        return workEvidenceRepository.findByAssignmentId(assignmentId);
    }

    public List<WorkEvidence> findByWorkerId(Long workerId) {
        return workEvidenceRepository.findByWorkerId(workerId);
    }
}
