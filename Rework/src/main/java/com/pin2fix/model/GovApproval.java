package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "gov_approvals")
public class GovApproval {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "gov_approval_id")
    private Long govApprovalId;

    @Column(name = "issue_id", nullable = false)
    private Long issueId;

    @Column(name = "gov_id")
    private Long govId;

    @Column(name = "approved_by")
    private Long approvedBy;

    @Column(columnDefinition = "TEXT")
    private String comment;

    @Column(name = "approved_at")
    private LocalDateTime approvedAt;

    public GovApproval() {}

    public GovApproval(Long govApprovalId, Long issueId, Long govId, Long approvedBy, 
                       String comment, LocalDateTime approvedAt) {
        this.govApprovalId = govApprovalId;
        this.issueId = issueId;
        this.govId = govId;
        this.approvedBy = approvedBy;
        this.comment = comment;
        this.approvedAt = approvedAt;
    }

    @PrePersist
    protected void onCreate() {
        approvedAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getGovApprovalId() { return govApprovalId; }
    public void setGovApprovalId(Long govApprovalId) { this.govApprovalId = govApprovalId; }
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public Long getGovId() { return govId; }
    public void setGovId(Long govId) { this.govId = govId; }
    public Long getApprovedBy() { return approvedBy; }
    public void setApprovedBy(Long approvedBy) { this.approvedBy = approvedBy; }
    public String getComment() { return comment; }
    public void setComment(String comment) { this.comment = comment; }
    public LocalDateTime getApprovedAt() { return approvedAt; }
    public void setApprovedAt(LocalDateTime approvedAt) { this.approvedAt = approvedAt; }

    // Builder
    public static GovApprovalBuilder builder() { return new GovApprovalBuilder(); }

    public static class GovApprovalBuilder {
        private Long govApprovalId;
        private Long issueId;
        private Long govId;
        private Long approvedBy;
        private String comment;
        private LocalDateTime approvedAt;

        public GovApprovalBuilder govApprovalId(Long govApprovalId) { this.govApprovalId = govApprovalId; return this; }
        public GovApprovalBuilder issueId(Long issueId) { this.issueId = issueId; return this; }
        public GovApprovalBuilder govId(Long govId) { this.govId = govId; return this; }
        public GovApprovalBuilder approvedBy(Long approvedBy) { this.approvedBy = approvedBy; return this; }
        public GovApprovalBuilder comment(String comment) { this.comment = comment; return this; }
        public GovApprovalBuilder approvedAt(LocalDateTime approvedAt) { this.approvedAt = approvedAt; return this; }

        public GovApproval build() {
            return new GovApproval(govApprovalId, issueId, govId, approvedBy, comment, approvedAt);
        }
    }
}
