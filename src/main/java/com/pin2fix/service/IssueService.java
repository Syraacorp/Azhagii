package com.pin2fix.service;

import com.pin2fix.entity.*;
import com.pin2fix.repository.*;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.multipart.MultipartFile;

import java.io.IOException;
import java.math.BigDecimal;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.LocalDateTime;
import java.util.Arrays;
import java.util.List;
import java.util.Optional;
import java.util.UUID;

@Service
@RequiredArgsConstructor
public class IssueService {
    
    private final IssueRepository issueRepository;
    private final PhotoRepository photoRepository;
    private final AssignmentRepository assignmentRepository;
    private final WorkEvidenceRepository workEvidenceRepository;
    private final HeadApprovalRepository headApprovalRepository;
    private final GovApprovalRepository govApprovalRepository;
    private final FeedbackRepository feedbackRepository;
    private final NotificationRepository notificationRepository;
    private final ActivityLogRepository activityLogRepository;
    private final UserRepository userRepository;
    private final GovernmentBodyRepository governmentBodyRepository;
    private final DepartmentRepository departmentRepository;
    
    public List<Issue> findAll() {
        return issueRepository.findAll();
    }
    
    public Optional<Issue> findById(Long id) {
        return issueRepository.findById(id);
    }
    
    public List<Issue> findByReporter(Long userId) {
        return issueRepository.findByReporterUserId(userId);
    }
    
    public List<Issue> findByGovId(Long govId) {
        return issueRepository.findAllByGovIdOrderByCreatedAtDesc(govId);
    }
    
    public List<Issue> findByDeptId(Long deptId) {
        return issueRepository.findAllByDeptIdOrderByCreatedAtDesc(deptId);
    }
    
    public List<Issue> findPendingForGov(Long govId) {
        return issueRepository.findByGovernmentBodyGovIdAndStatusIn(govId, 
            Arrays.asList(IssueStatus.PENDING, IssueStatus.HEAD_APPROVED));
    }
    
    public List<Issue> findForwardedForDept(Long deptId) {
        return issueRepository.findByDepartmentDeptIdAndStatus(deptId, IssueStatus.FORWARDED);
    }
    
    public List<Issue> findEvidenceSubmittedForHead(Long deptId) {
        return issueRepository.findByDepartmentDeptIdAndStatus(deptId, IssueStatus.EVIDENCE_SUBMITTED);
    }
    
    @Transactional
    public Issue createIssue(String title, String description, Integer severity,
                             BigDecimal latitude, BigDecimal longitude, String addressText,
                             Long reporterId, Long govId, MultipartFile[] photos) throws IOException {
        
        User reporter = userRepository.findById(reporterId).orElseThrow();
        GovernmentBody gov = governmentBodyRepository.findById(govId).orElseThrow();
        
        Issue issue = Issue.builder()
                .title(title)
                .description(description)
                .severity(severity)
                .latitude(latitude)
                .longitude(longitude)
                .addressText(addressText)
                .reporter(reporter)
                .governmentBody(gov)
                .status(IssueStatus.PENDING)
                .build();
        
        issue = issueRepository.save(issue);
        
        // Save photos
        if (photos != null) {
            for (MultipartFile photo : photos) {
                if (!photo.isEmpty()) {
                    String fileName = saveFile(photo, "issues/" + issue.getIssueId());
                    Photo p = Photo.builder()
                            .issue(issue)
                            .url("/uploads/" + fileName)
                            .caption(photo.getOriginalFilename())
                            .build();
                    photoRepository.save(p);
                }
            }
        }
        
        // Create notification for gov body users
        List<User> govUsers = userRepository.findByGovernmentBodyGovIdAndRole(govId, Role.GOV_BODY);
        for (User govUser : govUsers) {
            createNotification(govUser, issue, "New Issue Reported", 
                "New issue: " + title + " at " + addressText);
        }
        
        // Log activity
        logActivity(issue, reporter, "ISSUE_CREATED", "Citizen reported: " + title);
        
        return issue;
    }
    
    @Transactional
    public Issue forwardToDepartment(Long issueId, Long deptId, Long govUserId) {
        Issue issue = issueRepository.findById(issueId).orElseThrow();
        Department dept = departmentRepository.findById(deptId).orElseThrow();
        User govUser = userRepository.findById(govUserId).orElseThrow();
        
        issue.setDepartment(dept);
        issue.setStatus(IssueStatus.FORWARDED);
        issue = issueRepository.save(issue);
        
        // Notify dept heads
        List<User> deptHeads = userRepository.findByDepartmentDeptIdAndRole(deptId, Role.DEPT_HEAD);
        for (User head : deptHeads) {
            createNotification(head, issue, "Issue Forwarded", 
                "Issue #" + issueId + " forwarded to your department");
        }
        
        // Notify reporter
        createNotification(issue.getReporter(), issue, "Issue Forwarded", 
            "Your complaint has been forwarded to " + dept.getName());
        
        logActivity(issue, govUser, "ISSUE_FORWARDED", "Forwarded to " + dept.getName());
        
        return issue;
    }
    
    @Transactional
    public Assignment assignToAreaHead(Long issueId, Long areaHeadId, Long deptHeadId, String comment) {
        Issue issue = issueRepository.findById(issueId).orElseThrow();
        User areaHead = userRepository.findById(areaHeadId).orElseThrow();
        User deptHead = userRepository.findById(deptHeadId).orElseThrow();
        
        issue.setStatus(IssueStatus.ASSIGNED);
        issueRepository.save(issue);
        
        Assignment assignment = Assignment.builder()
                .issue(issue)
                .assignedBy(deptHead)
                .assignee(areaHead)
                .roleAssignee(RoleAssignee.AREA_HEAD)
                .comment(comment)
                .dueDate(LocalDateTime.now().plusDays(3))
                .status(AssignmentStatus.ASSIGNED)
                .build();
        
        assignment = assignmentRepository.save(assignment);
        
        createNotification(areaHead, issue, "New Assignment", 
            "Issue #" + issueId + " assigned to you");
        
        logActivity(issue, deptHead, "ASSIGNED_TO_AREA_HEAD", "Assigned to " + areaHead.getName());
        
        return assignment;
    }
    
    @Transactional
    public Assignment assignToWorker(Long issueId, Long workerId, Long areaHeadId, String comment) {
        Issue issue = issueRepository.findById(issueId).orElseThrow();
        User worker = userRepository.findById(workerId).orElseThrow();
        User areaHead = userRepository.findById(areaHeadId).orElseThrow();
        
        issue.setStatus(IssueStatus.IN_PROGRESS);
        issueRepository.save(issue);
        
        Assignment assignment = Assignment.builder()
                .issue(issue)
                .assignedBy(areaHead)
                .assignee(worker)
                .roleAssignee(RoleAssignee.WORKER)
                .comment(comment)
                .dueDate(LocalDateTime.now().plusDays(2))
                .status(AssignmentStatus.ASSIGNED)
                .build();
        
        assignment = assignmentRepository.save(assignment);
        
        createNotification(worker, issue, "New Work Assignment", 
            "Issue #" + issueId + " assigned to you for work");
        
        logActivity(issue, areaHead, "ASSIGNED_TO_WORKER", "Assigned to worker " + worker.getName());
        
        return assignment;
    }
    
    @Transactional
    public WorkEvidence submitWorkEvidence(Long assignmentId, Long workerId, 
                                           String notes, MultipartFile photo) throws IOException {
        Assignment assignment = assignmentRepository.findById(assignmentId).orElseThrow();
        User worker = userRepository.findById(workerId).orElseThrow();
        Issue issue = assignment.getIssue();
        
        String fileName = saveFile(photo, "evidence/" + assignmentId);
        
        WorkEvidence evidence = WorkEvidence.builder()
                .assignment(assignment)
                .worker(worker)
                .photoUrl("/uploads/" + fileName)
                .notes(notes)
                .build();
        
        evidence = workEvidenceRepository.save(evidence);
        
        assignment.setStatus(AssignmentStatus.COMPLETED);
        assignmentRepository.save(assignment);
        
        issue.setStatus(IssueStatus.EVIDENCE_SUBMITTED);
        issueRepository.save(issue);
        
        // Notify area head
        if (assignment.getAssignedBy() != null) {
            createNotification(assignment.getAssignedBy(), issue, "Work Evidence Submitted", 
                "Worker submitted evidence for Issue #" + issue.getIssueId());
        }
        
        logActivity(issue, worker, "EVIDENCE_SUBMITTED", "Worker submitted completion evidence");
        
        return evidence;
    }
    
    @Transactional
    public HeadApproval approveByHead(Long issueId, Long headId, ApprovalStatus status, String comment) {
        Issue issue = issueRepository.findById(issueId).orElseThrow();
        User head = userRepository.findById(headId).orElseThrow();
        
        HeadApproval approval = HeadApproval.builder()
                .issue(issue)
                .head(head)
                .status(status)
                .comment(comment)
                .build();
        
        approval = headApprovalRepository.save(approval);
        
        if (status == ApprovalStatus.APPROVED) {
            issue.setStatus(IssueStatus.HEAD_APPROVED);
            
            // Notify gov body
            List<User> govUsers = userRepository.findByGovernmentBodyGovIdAndRole(
                issue.getGovernmentBody().getGovId(), Role.GOV_BODY);
            for (User govUser : govUsers) {
                createNotification(govUser, issue, "Head Approval Complete", 
                    "Issue #" + issueId + " approved by department head. Awaiting final approval.");
            }
        } else {
            issue.setStatus(IssueStatus.REOPENED);
            
            // Notify worker for rework
            Assignment latestAssignment = assignmentRepository
                .findTopByIssueIssueIdOrderByAssignedAtDesc(issueId).orElse(null);
            if (latestAssignment != null && latestAssignment.getAssignee() != null) {
                createNotification(latestAssignment.getAssignee(), issue, "Rework Required", 
                    "Issue #" + issueId + " needs rework: " + comment);
            }
        }
        
        issueRepository.save(issue);
        logActivity(issue, head, status == ApprovalStatus.APPROVED ? "HEAD_APPROVED" : "HEAD_REJECTED", comment);
        
        return approval;
    }
    
    @Transactional
    public GovApproval approveByGov(Long issueId, Long govUserId, ApprovalStatus status, String comment) {
        Issue issue = issueRepository.findById(issueId).orElseThrow();
        User govUser = userRepository.findById(govUserId).orElseThrow();
        
        GovApproval approval = GovApproval.builder()
                .issue(issue)
                .govUser(govUser)
                .status(status)
                .comment(comment)
                .build();
        
        approval = govApprovalRepository.save(approval);
        
        if (status == ApprovalStatus.APPROVED) {
            issue.setStatus(IssueStatus.COMPLETED);
            
            // Notify citizen
            createNotification(issue.getReporter(), issue, "Issue Resolved!", 
                "Your complaint #" + issueId + " has been resolved. Please provide feedback.");
        } else {
            issue.setStatus(IssueStatus.REOPENED);
            
            // Notify dept head
            List<User> deptHeads = userRepository.findByDepartmentDeptIdAndRole(
                issue.getDepartment().getDeptId(), Role.DEPT_HEAD);
            for (User deptHead : deptHeads) {
                createNotification(deptHead, issue, "Issue Rejected", 
                    "Issue #" + issueId + " rejected by government body: " + comment);
            }
        }
        
        issueRepository.save(issue);
        logActivity(issue, govUser, status == ApprovalStatus.APPROVED ? "GOV_APPROVED" : "GOV_REJECTED", comment);
        
        return approval;
    }
    
    @Transactional
    public Feedback submitFeedback(Long issueId, Long userId, Integer rating, String message) {
        Issue issue = issueRepository.findById(issueId).orElseThrow();
        User user = userRepository.findById(userId).orElseThrow();
        
        boolean isPositive = rating >= 3;
        
        Feedback feedback = Feedback.builder()
                .issue(issue)
                .user(user)
                .rating(rating)
                .message(message)
                .isPositive(isPositive)
                .build();
        
        feedback = feedbackRepository.save(feedback);
        
        if (!isPositive) {
            issue.setStatus(IssueStatus.REOPENED);
            issueRepository.save(issue);
            
            // Notify gov body about negative feedback
            List<User> govUsers = userRepository.findByGovernmentBodyGovIdAndRole(
                issue.getGovernmentBody().getGovId(), Role.GOV_BODY);
            for (User govUser : govUsers) {
                createNotification(govUser, issue, "Negative Feedback Received", 
                    "Issue #" + issueId + " received negative feedback. Rework may be required.");
            }
        }
        
        logActivity(issue, user, "FEEDBACK_SUBMITTED", "Rating: " + rating + " - " + message);
        
        return feedback;
    }
    
    public List<Assignment> getAssignmentsForUser(Long userId) {
        return assignmentRepository.findByAssigneeUserIdOrderByAssignedAtDesc(userId);
    }
    
    public List<Photo> getPhotosForIssue(Long issueId) {
        return photoRepository.findByIssueIssueId(issueId);
    }
    
    public List<WorkEvidence> getEvidenceForAssignment(Long assignmentId) {
        return workEvidenceRepository.findByAssignmentAssignmentId(assignmentId);
    }
    
    public List<ActivityLog> getActivityLog(Long issueId) {
        return activityLogRepository.findByIssueIssueIdOrderByCreatedAtDesc(issueId);
    }
    
    public Optional<Feedback> getFeedbackForIssue(Long issueId) {
        List<Feedback> feedbacks = feedbackRepository.findByIssueIssueId(issueId);
        return feedbacks.isEmpty() ? Optional.empty() : Optional.of(feedbacks.get(0));
    }
    
    private void createNotification(User user, Issue issue, String title, String message) {
        if (user == null) return;
        Notification notification = Notification.builder()
                .user(user)
                .issue(issue)
                .title(title)
                .message(message)
                .isRead(false)
                .build();
        notificationRepository.save(notification);
    }
    
    private void logActivity(Issue issue, User actor, String action, String details) {
        ActivityLog log = ActivityLog.builder()
                .issue(issue)
                .actor(actor)
                .action(action)
                .details(details)
                .build();
        activityLogRepository.save(log);
    }
    
    private String saveFile(MultipartFile file, String subDir) throws IOException {
        String uploadDir = "uploads/" + subDir;
        Path uploadPath = Paths.get(uploadDir);
        
        if (!Files.exists(uploadPath)) {
            Files.createDirectories(uploadPath);
        }
        
        String fileName = UUID.randomUUID().toString() + "_" + file.getOriginalFilename();
        Path filePath = uploadPath.resolve(fileName);
        Files.copy(file.getInputStream(), filePath);
        
        return subDir + "/" + fileName;
    }
    
    public Long countByStatus(IssueStatus status) {
        return issueRepository.countByStatus(status);
    }
}
