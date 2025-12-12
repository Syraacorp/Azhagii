package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "issues")
public class Issue {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "issue_id")
    private Long issueId;

    @Column(nullable = false)
    private String title;

    @Column(columnDefinition = "TEXT")
    private String description;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private IssueStatus status = IssueStatus.PENDING;

    @Column(nullable = false)
    private Integer severity = 3;

    @Column(nullable = false)
    private Double latitude;

    @Column(nullable = false)
    private Double longitude;

    @Column(name = "address_text", length = 512)
    private String addressText;

    @Column(name = "reporter_id")
    private Long reporterId;

    @Column(name = "gov_id")
    private Long govId;

    @Column(name = "dept_id")
    private Long deptId;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    public Issue() {}

    public Issue(Long issueId, String title, String description, IssueStatus status, Integer severity,
                 Double latitude, Double longitude, String addressText, Long reporterId, Long govId, Long deptId,
                 LocalDateTime createdAt, LocalDateTime updatedAt) {
        this.issueId = issueId;
        this.title = title;
        this.description = description;
        this.status = status;
        this.severity = severity;
        this.latitude = latitude;
        this.longitude = longitude;
        this.addressText = addressText;
        this.reporterId = reporterId;
        this.govId = govId;
        this.deptId = deptId;
        this.createdAt = createdAt;
        this.updatedAt = updatedAt;
    }

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        updatedAt = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        updatedAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public String getTitle() { return title; }
    public void setTitle(String title) { this.title = title; }
    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }
    public IssueStatus getStatus() { return status; }
    public void setStatus(IssueStatus status) { this.status = status; }
    public Integer getSeverity() { return severity; }
    public void setSeverity(Integer severity) { this.severity = severity; }
    public Double getLatitude() { return latitude; }
    public void setLatitude(Double latitude) { this.latitude = latitude; }
    public Double getLongitude() { return longitude; }
    public void setLongitude(Double longitude) { this.longitude = longitude; }
    public String getAddressText() { return addressText; }
    public void setAddressText(String addressText) { this.addressText = addressText; }
    public Long getReporterId() { return reporterId; }
    public void setReporterId(Long reporterId) { this.reporterId = reporterId; }
    public Long getGovId() { return govId; }
    public void setGovId(Long govId) { this.govId = govId; }
    public Long getDeptId() { return deptId; }
    public void setDeptId(Long deptId) { this.deptId = deptId; }
    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }
    public LocalDateTime getUpdatedAt() { return updatedAt; }
    public void setUpdatedAt(LocalDateTime updatedAt) { this.updatedAt = updatedAt; }

    // Builder
    public static IssueBuilder builder() { return new IssueBuilder(); }

    public static class IssueBuilder {
        private Long issueId;
        private String title;
        private String description;
        private IssueStatus status = IssueStatus.PENDING;
        private Integer severity = 3;
        private Double latitude;
        private Double longitude;
        private String addressText;
        private Long reporterId;
        private Long govId;
        private Long deptId;
        private LocalDateTime createdAt;
        private LocalDateTime updatedAt;

        public IssueBuilder issueId(Long issueId) { this.issueId = issueId; return this; }
        public IssueBuilder title(String title) { this.title = title; return this; }
        public IssueBuilder description(String description) { this.description = description; return this; }
        public IssueBuilder status(IssueStatus status) { this.status = status; return this; }
        public IssueBuilder severity(Integer severity) { this.severity = severity; return this; }
        public IssueBuilder latitude(Double latitude) { this.latitude = latitude; return this; }
        public IssueBuilder longitude(Double longitude) { this.longitude = longitude; return this; }
        public IssueBuilder addressText(String addressText) { this.addressText = addressText; return this; }
        public IssueBuilder reporterId(Long reporterId) { this.reporterId = reporterId; return this; }
        public IssueBuilder govId(Long govId) { this.govId = govId; return this; }
        public IssueBuilder deptId(Long deptId) { this.deptId = deptId; return this; }
        public IssueBuilder createdAt(LocalDateTime createdAt) { this.createdAt = createdAt; return this; }
        public IssueBuilder updatedAt(LocalDateTime updatedAt) { this.updatedAt = updatedAt; return this; }

        public Issue build() {
            return new Issue(issueId, title, description, status, severity, latitude, longitude, 
                           addressText, reporterId, govId, deptId, createdAt, updatedAt);
        }
    }
}
