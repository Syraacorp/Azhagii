package com.pin2fix.service;

import com.pin2fix.model.*;
import com.pin2fix.repository.*;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;
import java.util.Optional;

@Service
@RequiredArgsConstructor
public class IssueService {
    private final IssueRepository issueRepository;
    private final PhotoRepository photoRepository;
    private final AssignmentRepository assignmentRepository;
    private final WorkEvidenceRepository workEvidenceRepository;
    private final HeadApprovalRepository headApprovalRepository;
    private final GovApprovalRepository govApprovalRepository;
    private final ActivityLogRepository activityLogRepository;
    private final NotificationRepository notificationRepository;
    private final FeedbackRepository feedbackRepository;

    public Issue createIssue(Issue issue) {
        issue.setStatus(IssueStatus.PENDING);
        return issueRepository.save(issue);
    }

    public Optional<Issue> findById(Long id) {
        return issueRepository.findById(id);
    }

    public List<Issue> findAll() {
        return issueRepository.findAll();
    }

    public List<Issue> findByReporterId(Long reporterId) {
        return issueRepository.findByReporterId(reporterId);
    }

    public List<Issue> findByStatus(IssueStatus status) {
        return issueRepository.findByStatus(status);
    }

    public List<Issue> findByGovId(Long govId) {
        return issueRepository.findByGovId(govId);
    }

    public List<Issue> findByDeptId(Long deptId) {
        return issueRepository.findByDeptId(deptId);
    }

    public List<Issue> findPendingIssues() {
        return issueRepository.findByStatus(IssueStatus.PENDING);
    }

    public List<Issue> findByGovIdAndStatus(Long govId, IssueStatus status) {
        return issueRepository.findByGovIdAndStatus(govId, status);
    }

    public List<Issue> findByDeptIdAndStatus(Long deptId, IssueStatus status) {
        return issueRepository.findByDeptIdAndStatus(deptId, status);
    }

    public List<Issue> findPendingGovApproval(Long govId) {
        return issueRepository.findByGovIdAndStatus(govId, IssueStatus.HEAD_APPROVED);
    }

    @Transactional
    public Issue updateStatus(Long issueId, IssueStatus newStatus, Long actorId) {
        Issue issue = issueRepository.findById(issueId)
                .orElseThrow(() -> new RuntimeException("Issue not found"));
        
        IssueStatus oldStatus = issue.getStatus();
        issue.setStatus(newStatus);
        issue = issueRepository.save(issue);

        // Log activity
        ActivityLog log = ActivityLog.builder()
                .issueId(issueId)
                .actorId(actorId)
                .action("STATUS_CHANGE")
                .details("Status changed from " + oldStatus + " to " + newStatus)
                .build();
        activityLogRepository.save(log);

        return issue;
    }

    @Transactional
    public Issue forwardToDepartment(Long issueId, Long deptId, Long govId, Long actorId) {
        Issue issue = issueRepository.findById(issueId)
                .orElseThrow(() -> new RuntimeException("Issue not found"));
        
        issue.setDeptId(deptId);
        issue.setGovId(govId);
        issue.setStatus(IssueStatus.FORWARDED);
        issue = issueRepository.save(issue);

        // Log activity
        ActivityLog log = ActivityLog.builder()
                .issueId(issueId)
                .actorId(actorId)
                .action("FORWARDED_TO_DEPARTMENT")
                .details("Issue forwarded to department ID: " + deptId)
                .build();
        activityLogRepository.save(log);

        return issue;
    }

    public Issue save(Issue issue) {
        return issueRepository.save(issue);
    }

    // Photo methods
    public Photo addPhoto(Photo photo) {
        return photoRepository.save(photo);
    }

    public List<Photo> getPhotosByIssueId(Long issueId) {
        return photoRepository.findByIssueId(issueId);
    }

    // Statistics
    public long countByStatus(IssueStatus status) {
        return issueRepository.countByStatus(status);
    }

    public long countByReporterId(Long reporterId) {
        return issueRepository.countByReporterId(reporterId);
    }

    public long countAll() {
        return issueRepository.count();
    }
}
