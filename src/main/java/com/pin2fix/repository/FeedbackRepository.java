package com.pin2fix.repository;

import com.pin2fix.entity.Feedback;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface FeedbackRepository extends JpaRepository<Feedback, Long> {
    List<Feedback> findByIssueIssueId(Long issueId);
    Optional<Feedback> findByIssueIssueIdAndUserUserId(Long issueId, Long userId);
    List<Feedback> findByUserUserId(Long userId);
}
