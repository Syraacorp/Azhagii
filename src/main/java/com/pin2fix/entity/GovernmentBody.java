package com.pin2fix.entity;

import jakarta.persistence.*;
import lombok.*;
import java.time.LocalDateTime;
import java.util.List;

@Entity
@Table(name = "government_bodies")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class GovernmentBody {
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "gov_id")
    private Long govId;
    
    @Column(nullable = false)
    private String name;
    
    private String jurisdiction;
    
    @Column(columnDefinition = "TEXT")
    private String address;
    
    @Column(name = "contact_phone", length = 32)
    private String contactPhone;
    
    @Column(name = "contact_email")
    private String contactEmail;
    
    @Column(name = "is_active")
    private Boolean isActive = true;
    
    @Column(name = "created_at")
    private LocalDateTime createdAt;
    
    @OneToMany(mappedBy = "governmentBody", cascade = CascadeType.ALL)
    private List<Department> departments;
    
    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        if (isActive == null) isActive = true;
    }
}
