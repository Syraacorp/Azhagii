package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "assignments")
public class Assignment {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "assignment_id")
    private Long assignmentId;

    @Column(name = "issue_id", nullable = false)
    private Long issueId;

    @Column(name = "assigned_by")
    private Long assignedBy;

    @Column(name = "assignee_id")
    private Long assigneeId;

    @Enumerated(EnumType.STRING)
    @Column(name = "role_assignee")
    private Role roleAssignee;

    @Column(name = "assigned_at")
    private LocalDateTime assignedAt;

    @Column(name = "due_date")
    private LocalDateTime dueDate;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private AssignmentStatus status = AssignmentStatus.ASSIGNED;

    @Column(columnDefinition = "TEXT")
    private String comment;

    public Assignment() {}

    public Assignment(Long assignmentId, Long issueId, Long assignedBy, Long assigneeId, Role roleAssignee,
                      LocalDateTime assignedAt, LocalDateTime dueDate, AssignmentStatus status, String comment) {
        this.assignmentId = assignmentId;
        this.issueId = issueId;
        this.assignedBy = assignedBy;
        this.assigneeId = assigneeId;
        this.roleAssignee = roleAssignee;
        this.assignedAt = assignedAt;
        this.dueDate = dueDate;
        this.status = status;
        this.comment = comment;
    }

    @PrePersist
    protected void onCreate() {
        assignedAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getAssignmentId() { return assignmentId; }
    public void setAssignmentId(Long assignmentId) { this.assignmentId = assignmentId; }
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public Long getAssignedBy() { return assignedBy; }
    public void setAssignedBy(Long assignedBy) { this.assignedBy = assignedBy; }
    public Long getAssigneeId() { return assigneeId; }
    public void setAssigneeId(Long assigneeId) { this.assigneeId = assigneeId; }
    public Role getRoleAssignee() { return roleAssignee; }
    public void setRoleAssignee(Role roleAssignee) { this.roleAssignee = roleAssignee; }
    public LocalDateTime getAssignedAt() { return assignedAt; }
    public void setAssignedAt(LocalDateTime assignedAt) { this.assignedAt = assignedAt; }
    public LocalDateTime getDueDate() { return dueDate; }
    public void setDueDate(LocalDateTime dueDate) { this.dueDate = dueDate; }
    public AssignmentStatus getStatus() { return status; }
    public void setStatus(AssignmentStatus status) { this.status = status; }
    public String getComment() { return comment; }
    public void setComment(String comment) { this.comment = comment; }

    // Builder
    public static AssignmentBuilder builder() { return new AssignmentBuilder(); }

    public static class AssignmentBuilder {
        private Long assignmentId;
        private Long issueId;
        private Long assignedBy;
        private Long assigneeId;
        private Role roleAssignee;
        private LocalDateTime assignedAt;
        private LocalDateTime dueDate;
        private AssignmentStatus status = AssignmentStatus.ASSIGNED;
        private String comment;

        public AssignmentBuilder assignmentId(Long assignmentId) { this.assignmentId = assignmentId; return this; }
        public AssignmentBuilder issueId(Long issueId) { this.issueId = issueId; return this; }
        public AssignmentBuilder assignedBy(Long assignedBy) { this.assignedBy = assignedBy; return this; }
        public AssignmentBuilder assigneeId(Long assigneeId) { this.assigneeId = assigneeId; return this; }
        public AssignmentBuilder roleAssignee(Role roleAssignee) { this.roleAssignee = roleAssignee; return this; }
        public AssignmentBuilder assignedAt(LocalDateTime assignedAt) { this.assignedAt = assignedAt; return this; }
        public AssignmentBuilder dueDate(LocalDateTime dueDate) { this.dueDate = dueDate; return this; }
        public AssignmentBuilder status(AssignmentStatus status) { this.status = status; return this; }
        public AssignmentBuilder comment(String comment) { this.comment = comment; return this; }

        public Assignment build() {
            return new Assignment(assignmentId, issueId, assignedBy, assigneeId, roleAssignee, 
                                 assignedAt, dueDate, status, comment);
        }
    }
}
