package com.pin2fix.dto;

import lombok.Data;

@Data
public class FeedbackRequest {
    private Long issueId;
    private Long userId;
    private Integer rating;
    private String message;
    private Boolean isPositive;
}
