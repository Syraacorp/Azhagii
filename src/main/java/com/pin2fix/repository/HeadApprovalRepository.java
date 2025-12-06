package com.pin2fix.repository;

import com.pin2fix.model.HeadApproval;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface HeadApprovalRepository extends JpaRepository<HeadApproval, Long> {
    List<HeadApproval> findByAssignmentId(Long assignmentId);
    Optional<HeadApproval> findTopByAssignmentIdOrderByApprovedAtDesc(Long assignmentId);
    List<HeadApproval> findByApprovedBy(Long approvedBy);
}
