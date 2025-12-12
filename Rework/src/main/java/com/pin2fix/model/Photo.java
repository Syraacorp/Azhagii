package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "photos")
public class Photo {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "photo_id")
    private Long photoId;

    @Column(name = "issue_id", nullable = false)
    private Long issueId;

    @Column(nullable = false, columnDefinition = "TEXT")
    private String url;

    private String caption;

    @Column(name = "uploaded_at")
    private LocalDateTime uploadedAt;

    public Photo() {}

    public Photo(Long photoId, Long issueId, String url, String caption, LocalDateTime uploadedAt) {
        this.photoId = photoId;
        this.issueId = issueId;
        this.url = url;
        this.caption = caption;
        this.uploadedAt = uploadedAt;
    }

    @PrePersist
    protected void onCreate() {
        uploadedAt = LocalDateTime.now();
    }

    // Getters and Setters
    public Long getPhotoId() { return photoId; }
    public void setPhotoId(Long photoId) { this.photoId = photoId; }
    public Long getIssueId() { return issueId; }
    public void setIssueId(Long issueId) { this.issueId = issueId; }
    public String getUrl() { return url; }
    public void setUrl(String url) { this.url = url; }
    public String getCaption() { return caption; }
    public void setCaption(String caption) { this.caption = caption; }
    public LocalDateTime getUploadedAt() { return uploadedAt; }
    public void setUploadedAt(LocalDateTime uploadedAt) { this.uploadedAt = uploadedAt; }

    // Builder
    public static PhotoBuilder builder() { return new PhotoBuilder(); }

    public static class PhotoBuilder {
        private Long photoId;
        private Long issueId;
        private String url;
        private String caption;
        private LocalDateTime uploadedAt;

        public PhotoBuilder photoId(Long photoId) { this.photoId = photoId; return this; }
        public PhotoBuilder issueId(Long issueId) { this.issueId = issueId; return this; }
        public PhotoBuilder url(String url) { this.url = url; return this; }
        public PhotoBuilder caption(String caption) { this.caption = caption; return this; }
        public PhotoBuilder uploadedAt(LocalDateTime uploadedAt) { this.uploadedAt = uploadedAt; return this; }

        public Photo build() {
            return new Photo(photoId, issueId, url, caption, uploadedAt);
        }
    }
}
