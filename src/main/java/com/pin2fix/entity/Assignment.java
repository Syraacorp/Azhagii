package com.pin2fix.entity;

import jakarta.persistence.*;
import lombok.*;
import java.time.LocalDateTime;
import java.util.List;

@Entity
@Table(name = "assignments")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Assignment {
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "assignment_id")
    private Long assignmentId;
    
    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "issue_id", nullable = false)
    private Issue issue;
    
    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "assigned_by")
    private User assignedBy;
    
    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "assignee_id")
    private User assignee;
    
    @Enumerated(EnumType.STRING)
    @Column(name = "role_assignee")
    private RoleAssignee roleAssignee;
    
    @Column(name = "assigned_at")
    private LocalDateTime assignedAt;
    
    @Column(name = "due_date")
    private LocalDateTime dueDate;
    
    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private AssignmentStatus status = AssignmentStatus.ASSIGNED;
    
    @Column(columnDefinition = "TEXT")
    private String comment;
    
    @OneToMany(mappedBy = "assignment", cascade = CascadeType.ALL, fetch = FetchType.LAZY)
    private List<WorkEvidence> workEvidences;
    
    @PrePersist
    protected void onCreate() {
        assignedAt = LocalDateTime.now();
        if (status == null) status = AssignmentStatus.ASSIGNED;
    }
}
