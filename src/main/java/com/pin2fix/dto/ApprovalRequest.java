package com.pin2fix.dto;

import com.pin2fix.model.ApprovalStatus;

public class ApprovalRequest {
    private Long assignmentId;
    private Long issueId;
    private Long approvedBy;
    private Long govId;
    private ApprovalStatus status;
    private String comment;

    public ApprovalRequest() {}

    // Getters and Setters
    public Long getAssignmentId() { return assignmentId; }
    public void setAssignmentId(Long assignmentId) { this.assignmentId = assignmentId; }
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public Long getApprovedBy() { return approvedBy; }
    public void setApprovedBy(Long approvedBy) { this.approvedBy = approvedBy; }
    public Long getGovId() { return govId; }
    public void setGovId(Long govId) { this.govId = govId; }
    public ApprovalStatus getStatus() { return status; }
    public void setStatus(ApprovalStatus status) { this.status = status; }
    public String getComment() { return comment; }
    public void setComment(String comment) { this.comment = comment; }
}
