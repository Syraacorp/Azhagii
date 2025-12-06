package com.pin2fix.dto;

import lombok.Data;

@Data
public class IssueRequest {
    private String title;
    private String description;
    private Integer severity;
    private Double latitude;
    private Double longitude;
    private String addressText;
    private Long reporterId;
}
