package com.pin2fix.dto;

import com.pin2fix.model.ApprovalStatus;
import lombok.Data;

@Data
public class ApprovalRequest {
    private Long assignmentId;
    private Long issueId;
    private Long approvedBy;
    private Long govId;
    private ApprovalStatus status;
    private String comment;
}
