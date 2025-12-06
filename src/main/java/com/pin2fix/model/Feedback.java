package com.pin2fix.model;

import jakarta.persistence.*;
import lombok.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "feedback")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Feedback {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "feedback_id")
    private Long feedbackId;

    @Column(name = "issue_id", nullable = false)
    private Long issueId;

    @Column(name = "user_id")
    private Long userId;

    private Integer rating;

    @Column(columnDefinition = "TEXT")
    private String message;

    @Column(name = "is_positive")
    private Boolean isPositive;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }
}
