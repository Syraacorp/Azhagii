package com.pin2fix.dto;

import com.pin2fix.model.Role;

public class AssignmentRequest {
    private Long issueId;
    private Long assignedBy;
    private Long assigneeId;
    private Role roleAssignee;
    private String comment;

    public AssignmentRequest() {}

    // Getters and Setters
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public Long getAssignedBy() { return assignedBy; }
    public void setAssignedBy(Long assignedBy) { this.assignedBy = assignedBy; }
    public Long getAssigneeId() { return assigneeId; }
    public void setAssigneeId(Long assigneeId) { this.assigneeId = assigneeId; }
    public Role getRoleAssignee() { return roleAssignee; }
    public void setRoleAssignee(Role roleAssignee) { this.roleAssignee = roleAssignee; }
    public String getComment() { return comment; }
    public void setComment(String comment) { this.comment = comment; }
}
