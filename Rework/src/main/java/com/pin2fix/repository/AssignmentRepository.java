package com.pin2fix.repository;

import com.pin2fix.model.Assignment;
import com.pin2fix.model.AssignmentStatus;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface AssignmentRepository extends JpaRepository<Assignment, Long> {
    List<Assignment> findByIssueId(Long issueId);
    List<Assignment> findByAssigneeId(Long assigneeId);
    List<Assignment> findByAssigneeIdAndStatus(Long assigneeId, AssignmentStatus status);
    Optional<Assignment> findTopByIssueIdOrderByAssignedAtDesc(Long issueId);
    List<Assignment> findByAssigneeIdAndStatusIn(Long assigneeId, List<AssignmentStatus> statuses);
    long countByAssigneeId(Long assigneeId);
    long countByAssigneeIdAndStatus(Long assigneeId, AssignmentStatus status);
}
