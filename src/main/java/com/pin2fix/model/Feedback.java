package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "feedback")
public class Feedback {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "feedback_id")
    private Long feedbackId;

    @Column(name = "issue_id", nullable = false)
    private Long issueId;

    @Column(name = "user_id")
    private Long userId;

    private Integer rating;

    @Column(columnDefinition = "TEXT")
    private String message;

    @Column(name = "is_positive")
    private Boolean isPositive;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    public Feedback() {}

    public Feedback(Long feedbackId, Long issueId, Long userId, Integer rating, 
                    String message, Boolean isPositive, LocalDateTime createdAt) {
        this.feedbackId = feedbackId;
        this.issueId = issueId;
        this.userId = userId;
        this.rating = rating;
        this.message = message;
        this.isPositive = isPositive;
        this.createdAt = createdAt;
    }

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getFeedbackId() { return feedbackId; }
    public void setFeedbackId(Long feedbackId) { this.feedbackId = feedbackId; }
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
    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    // Builder
    public static FeedbackBuilder builder() { return new FeedbackBuilder(); }

    public static class FeedbackBuilder {
        private Long feedbackId;
        private Long issueId;
        private Long userId;
        private Integer rating;
        private String message;
        private Boolean isPositive;
        private LocalDateTime createdAt;

        public FeedbackBuilder feedbackId(Long feedbackId) { this.feedbackId = feedbackId; return this; }
        public FeedbackBuilder issueId(Long issueId) { this.issueId = issueId; return this; }
        public FeedbackBuilder userId(Long userId) { this.userId = userId; return this; }
        public FeedbackBuilder rating(Integer rating) { this.rating = rating; return this; }
        public FeedbackBuilder message(String message) { this.message = message; return this; }
        public FeedbackBuilder isPositive(Boolean isPositive) { this.isPositive = isPositive; return this; }
        public FeedbackBuilder createdAt(LocalDateTime createdAt) { this.createdAt = createdAt; return this; }

        public Feedback build() {
            return new Feedback(feedbackId, issueId, userId, rating, message, isPositive, createdAt);
        }
    }
}
