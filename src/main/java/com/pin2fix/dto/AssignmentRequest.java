package com.pin2fix.dto;

import com.pin2fix.model.Role;
import lombok.Data;

@Data
public class AssignmentRequest {
    private Long issueId;
    private Long assignedBy;
    private Long assigneeId;
    private Role roleAssignee;
    private String comment;
}
