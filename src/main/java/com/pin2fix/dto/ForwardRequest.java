package com.pin2fix.dto;

import lombok.Data;

@Data
public class ForwardRequest {
    private Long issueId;
    private Long deptId;
    private Long govId;
    private Long actorId;
}
