package com.pin2fix.repository;

import com.pin2fix.model.Feedback;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface FeedbackRepository extends JpaRepository<Feedback, Long> {
    List<Feedback> findByIssueId(Long issueId);
    List<Feedback> findByUserId(Long userId);
    Optional<Feedback> findByIssueIdAndUserId(Long issueId, Long userId);
    boolean existsByIssueIdAndUserId(Long issueId, Long userId);
}
