package com.pin2fix.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "departments")
public class Department {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "dept_id")
    private Long deptId;

    @Column(name = "gov_id", nullable = false)
    private Long govId;

    @Column(nullable = false)
    private String name;

    private String description;

    @Column(name = "contact_email")
    private String contactEmail;

    @Column(name = "is_active")
    private Boolean isActive = true;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    public Department() {}

    public Department(Long deptId, Long govId, String name, String description, 
                      String contactEmail, Boolean isActive, LocalDateTime createdAt) {
        this.deptId = deptId;
        this.govId = govId;
        this.name = name;
        this.description = description;
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
    public Long getDeptId() { return deptId; }
    public void setDeptId(Long deptId) { this.deptId = deptId; }
    public Long getGovId() { return govId; }
    public void setGovId(Long govId) { this.govId = govId; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }
    public String getContactEmail() { return contactEmail; }
    public void setContactEmail(String contactEmail) { this.contactEmail = contactEmail; }
    public Boolean getIsActive() { return isActive; }
    public void setIsActive(Boolean isActive) { this.isActive = isActive; }
    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    // Builder
    public static DepartmentBuilder builder() { return new DepartmentBuilder(); }

    public static class DepartmentBuilder {
        private Long deptId;
        private Long govId;
        private String name;
        private String description;
        private String contactEmail;
        private Boolean isActive = true;
        private LocalDateTime createdAt;

        public DepartmentBuilder deptId(Long deptId) { this.deptId = deptId; return this; }
        public DepartmentBuilder govId(Long govId) { this.govId = govId; return this; }
        public DepartmentBuilder name(String name) { this.name = name; return this; }
        public DepartmentBuilder description(String description) { this.description = description; return this; }
        public DepartmentBuilder contactEmail(String contactEmail) { this.contactEmail = contactEmail; return this; }
        public DepartmentBuilder isActive(Boolean isActive) { this.isActive = isActive; return this; }
        public DepartmentBuilder createdAt(LocalDateTime createdAt) { this.createdAt = createdAt; return this; }

        public Department build() {
            return new Department(deptId, govId, name, description, contactEmail, isActive, createdAt);
        }
    }
}
