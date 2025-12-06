package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "work_evidence")
public class WorkEvidence {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "evidence_id")
    private Long evidenceId;

    @Column(name = "assignment_id", nullable = false)
    private Long assignmentId;

    @Column(name = "worker_id")
    private Long workerId;

    @Column(nullable = false, columnDefinition = "TEXT")
    private String url;

    @Column(columnDefinition = "TEXT")
    private String notes;

    @Column(name = "uploaded_at")
    private LocalDateTime uploadedAt;

    public WorkEvidence() {}

    public WorkEvidence(Long evidenceId, Long assignmentId, Long workerId, String url, 
                        String notes, LocalDateTime uploadedAt) {
        this.evidenceId = evidenceId;
        this.assignmentId = assignmentId;
        this.workerId = workerId;
        this.url = url;
        this.notes = notes;
        this.uploadedAt = uploadedAt;
    }

    @PrePersist
    protected void onCreate() {
        uploadedAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getEvidenceId() { return evidenceId; }
    public void setEvidenceId(Long evidenceId) { this.evidenceId = evidenceId; }
    public Long getAssignmentId() { return assignmentId; }
    public void setAssignmentId(Long assignmentId) { this.assignmentId = assignmentId; }
    public Long getWorkerId() { return workerId; }
    public void setWorkerId(Long workerId) { this.workerId = workerId; }
    public String getUrl() { return url; }
    public void setUrl(String url) { this.url = url; }
    public String getNotes() { return notes; }
    public void setNotes(String notes) { this.notes = notes; }
    public LocalDateTime getUploadedAt() { return uploadedAt; }
    public void setUploadedAt(LocalDateTime uploadedAt) { this.uploadedAt = uploadedAt; }

    // Builder
    public static WorkEvidenceBuilder builder() { return new WorkEvidenceBuilder(); }

    public static class WorkEvidenceBuilder {
        private Long evidenceId;
        private Long assignmentId;
        private Long workerId;
        private String url;
        private String notes;
        private LocalDateTime uploadedAt;

        public WorkEvidenceBuilder evidenceId(Long evidenceId) { this.evidenceId = evidenceId; return this; }
        public WorkEvidenceBuilder assignmentId(Long assignmentId) { this.assignmentId = assignmentId; return this; }
        public WorkEvidenceBuilder workerId(Long workerId) { this.workerId = workerId; return this; }
        public WorkEvidenceBuilder url(String url) { this.url = url; return this; }
        public WorkEvidenceBuilder notes(String notes) { this.notes = notes; return this; }
        public WorkEvidenceBuilder uploadedAt(LocalDateTime uploadedAt) { this.uploadedAt = uploadedAt; return this; }

        public WorkEvidence build() {
            return new WorkEvidence(evidenceId, assignmentId, workerId, url, notes, uploadedAt);
        }
    }
}
