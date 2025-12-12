package com.pin2fix.repository;

import com.pin2fix.entity.WorkEvidence;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface WorkEvidenceRepository extends JpaRepository<WorkEvidence, Long> {
    List<WorkEvidence> findByAssignmentAssignmentId(Long assignmentId);
    List<WorkEvidence> findByWorkerUserId(Long workerId);
}
