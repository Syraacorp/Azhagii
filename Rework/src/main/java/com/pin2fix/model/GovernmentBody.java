package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "government_bodies")
public class GovernmentBody {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "gov_id")
    private Long govId;

    @Column(nullable = false)
    private String name;

    private String jurisdiction;

    private String address;

    @Column(name = "contact_phone")
    private String contactPhone;

    @Column(name = "contact_email")
    private String contactEmail;

    @Column(name = "is_active")
    private Boolean isActive = true;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    public GovernmentBody() {}

    public GovernmentBody(Long govId, String name, String jurisdiction, String address,
                          String contactPhone, String contactEmail, Boolean isActive, LocalDateTime createdAt) {
        this.govId = govId;
        this.name = name;
        this.jurisdiction = jurisdiction;
        this.address = address;
        this.contactPhone = contactPhone;
        this.contactEmail = contactEmail;
        this.isActive = isActive;
        this.createdAt = createdAt;
    }

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        if (isActive == null) isActive = true;
    }

    // Getters and Setters
    public Long getGovId() { return govId; }
    public void setGovId(Long govId) { this.govId = govId; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getJurisdiction() { return jurisdiction; }
    public void setJurisdiction(String jurisdiction) { this.jurisdiction = jurisdiction; }
    public String getAddress() { return address; }
    public void setAddress(String address) { this.address = address; }
    public String getContactPhone() { return contactPhone; }
    public void setContactPhone(String contactPhone) { this.contactPhone = contactPhone; }
    public String getContactEmail() { return contactEmail; }
    public void setContactEmail(String contactEmail) { this.contactEmail = contactEmail; }
    public Boolean getIsActive() { return isActive; }
    public void setIsActive(Boolean isActive) { this.isActive = isActive; }
    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    // Builder
    public static GovernmentBodyBuilder builder() { return new GovernmentBodyBuilder(); }

    public static class GovernmentBodyBuilder {
        private Long govId;
        private String name;
        private String jurisdiction;
        private String address;
        private String contactPhone;
        private String contactEmail;
        private Boolean isActive = true;
        private LocalDateTime createdAt;

        public GovernmentBodyBuilder govId(Long govId) { this.govId = govId; return this; }
        public GovernmentBodyBuilder name(String name) { this.name = name; return this; }
        public GovernmentBodyBuilder jurisdiction(String jurisdiction) { this.jurisdiction = jurisdiction; return this; }
        public GovernmentBodyBuilder address(String address) { this.address = address; return this; }
        public GovernmentBodyBuilder contactPhone(String contactPhone) { this.contactPhone = contactPhone; return this; }
        public GovernmentBodyBuilder contactEmail(String contactEmail) { this.contactEmail = contactEmail; return this; }
        public GovernmentBodyBuilder isActive(Boolean isActive) { this.isActive = isActive; return this; }
        public GovernmentBodyBuilder createdAt(LocalDateTime createdAt) { this.createdAt = createdAt; return this; }

        public GovernmentBody build() {
            return new GovernmentBody(govId, name, jurisdiction, address, contactPhone, contactEmail, isActive, createdAt);
        }
    }
}
