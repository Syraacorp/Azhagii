package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "head_approvals")
public class HeadApproval {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "approval_id")
    private Long approvalId;

    @Column(name = "assignment_id", nullable = false)
    private Long assignmentId;

    @Column(name = "approved_by")
    private Long approvedBy;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private ApprovalStatus status;

    @Column(columnDefinition = "TEXT")
    private String comment;

    @Column(name = "approved_at")
    private LocalDateTime approvedAt;

    public HeadApproval() {}

    public HeadApproval(Long approvalId, Long assignmentId, Long approvedBy, 
                        ApprovalStatus status, String comment, LocalDateTime approvedAt) {
        this.approvalId = approvalId;
        this.assignmentId = assignmentId;
        this.approvedBy = approvedBy;
        this.status = status;
        this.comment = comment;
        this.approvedAt = approvedAt;
    }

    @PrePersist
    protected void onCreate() {
        approvedAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getApprovalId() { return approvalId; }
    public void setApprovalId(Long approvalId) { this.approvalId = approvalId; }
    public Long getAssignmentId() { return assignmentId; }
    public void setAssignmentId(Long assignmentId) { this.assignmentId = assignmentId; }
    public Long getApprovedBy() { return approvedBy; }
    public void setApprovedBy(Long approvedBy) { this.approvedBy = approvedBy; }
    public ApprovalStatus getStatus() { return status; }
    public void setStatus(ApprovalStatus status) { this.status = status; }
    public String getComment() { return comment; }
    public void setComment(String comment) { this.comment = comment; }
    public LocalDateTime getApprovedAt() { return approvedAt; }
    public void setApprovedAt(LocalDateTime approvedAt) { this.approvedAt = approvedAt; }

    // Builder
    public static HeadApprovalBuilder builder() { return new HeadApprovalBuilder(); }

    public static class HeadApprovalBuilder {
        private Long approvalId;
        private Long assignmentId;
        private Long approvedBy;
        private ApprovalStatus status;
        private String comment;
        private LocalDateTime approvedAt;

        public HeadApprovalBuilder approvalId(Long approvalId) { this.approvalId = approvalId; return this; }
        public HeadApprovalBuilder assignmentId(Long assignmentId) { this.assignmentId = assignmentId; return this; }
        public HeadApprovalBuilder approvedBy(Long approvedBy) { this.approvedBy = approvedBy; return this; }
        public HeadApprovalBuilder status(ApprovalStatus status) { this.status = status; return this; }
        public HeadApprovalBuilder comment(String comment) { this.comment = comment; return this; }
        public HeadApprovalBuilder approvedAt(LocalDateTime approvedAt) { this.approvedAt = approvedAt; return this; }

        public HeadApproval build() {
            return new HeadApproval(approvalId, assignmentId, approvedBy, status, comment, approvedAt);
        }
    }
}
