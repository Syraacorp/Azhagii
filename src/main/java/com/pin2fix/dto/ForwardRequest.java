package com.pin2fix.dto;

public class ForwardRequest {
    private Long issueId;
    private Long deptId;
    private Long govId;
    private Long actorId;

    public ForwardRequest() {}

    // Getters and Setters
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public Long getDeptId() { return deptId; }
    public void setDeptId(Long deptId) { this.deptId = deptId; }
    public Long getGovId() { return govId; }
    public void setGovId(Long govId) { this.govId = govId; }
    public Long getActorId() { return actorId; }
    public void setActorId(Long actorId) { this.actorId = actorId; }
}
