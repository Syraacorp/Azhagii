package com.pin2fix.model;

import jakarta.persistence.*;
import lombok.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "head_approvals")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class HeadApproval {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "approval_id")
    private Long approvalId;

    @Column(name = "assignment_id", nullable = false)
    private Long assignmentId;

    @Column(name = "approved_by")
    private Long approvedBy;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private ApprovalStatus status;

    @Column(columnDefinition = "TEXT")
    private String comment;

    @Column(name = "approved_at")
    private LocalDateTime approvedAt;

    @PrePersist
    protected void onCreate() {
        approvedAt = LocalDateTime.now();
    }
}
