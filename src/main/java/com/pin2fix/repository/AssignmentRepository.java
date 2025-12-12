package com.pin2fix.repository;

import com.pin2fix.entity.Assignment;
import com.pin2fix.entity.AssignmentStatus;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface AssignmentRepository extends JpaRepository<Assignment, Long> {
    List<Assignment> findByIssueIssueId(Long issueId);
    List<Assignment> findByAssigneeUserId(Long userId);
    List<Assignment> findByAssigneeUserIdAndStatus(Long userId, AssignmentStatus status);
    Optional<Assignment> findTopByIssueIssueIdOrderByAssignedAtDesc(Long issueId);
    List<Assignment> findByAssigneeUserIdOrderByAssignedAtDesc(Long userId);
}
