package com.pin2fix.model;

import jakarta.persistence.*;
import lombok.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "gov_approvals")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class GovApproval {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "gov_approval_id")
    private Long govApprovalId;

    @Column(name = "issue_id", nullable = false)
    private Long issueId;

    @Column(name = "gov_id")
    private Long govId;

    @Column(name = "approved_by")
    private Long approvedBy;

    @Column(columnDefinition = "TEXT")
    private String comment;

    @Column(name = "approved_at")
    private LocalDateTime approvedAt;

    @PrePersist
    protected void onCreate() {
        approvedAt = LocalDateTime.now();
    }
}
