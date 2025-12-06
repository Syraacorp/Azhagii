package com.pin2fix.dto;

public class FeedbackRequest {
    private Long issueId;
    private Long userId;
    private Integer rating;
    private String message;
    private Boolean isPositive;

    public FeedbackRequest() {}

    // Getters and Setters
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public Long getUserId() { return userId; }
    public void setUserId(Long userId) { this.userId = userId; }
    public Integer getRating() { return rating; }
    public void setRating(Integer rating) { this.rating = rating; }
    public String getMessage() { return message; }
    public void setMessage(String message) { this.message = message; }
    public Boolean getIsPositive() { return isPositive; }
    public void setIsPositive(Boolean isPositive) { this.isPositive = isPositive; }
}
