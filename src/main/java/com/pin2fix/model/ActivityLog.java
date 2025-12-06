package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "activity_logs")
public class ActivityLog {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "log_id")
    private Long logId;

    @Column(name = "issue_id")
    private Long issueId;

    @Column(name = "actor_id")
    private Long actorId;

    private String action;

    @Column(columnDefinition = "TEXT")
    private String details;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    public ActivityLog() {}

    public ActivityLog(Long logId, Long issueId, Long actorId, String action, 
                       String details, LocalDateTime createdAt) {
        this.logId = logId;
        this.issueId = issueId;
        this.actorId = actorId;
        this.action = action;
        this.details = details;
        this.createdAt = createdAt;
    }

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getLogId() { return logId; }
    public void setLogId(Long logId) { this.logId = logId; }
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public Long getActorId() { return actorId; }
    public void setActorId(Long actorId) { this.actorId = actorId; }
    public String getAction() { return action; }
    public void setAction(String action) { this.action = action; }
    public String getDetails() { return details; }
    public void setDetails(String details) { this.details = details; }
    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    // Builder
    public static ActivityLogBuilder builder() { return new ActivityLogBuilder(); }

    public static class ActivityLogBuilder {
        private Long logId;
        private Long issueId;
        private Long actorId;
        private String action;
        private String details;
        private LocalDateTime createdAt;

        public ActivityLogBuilder logId(Long logId) { this.logId = logId; return this; }
        public ActivityLogBuilder issueId(Long issueId) { this.issueId = issueId; return this; }
        public ActivityLogBuilder actorId(Long actorId) { this.actorId = actorId; return this; }
        public ActivityLogBuilder action(String action) { this.action = action; return this; }
        public ActivityLogBuilder details(String details) { this.details = details; return this; }
        public ActivityLogBuilder createdAt(LocalDateTime createdAt) { this.createdAt = createdAt; return this; }

        public ActivityLog build() {
            return new ActivityLog(logId, issueId, actorId, action, details, createdAt);
        }
    }
}
