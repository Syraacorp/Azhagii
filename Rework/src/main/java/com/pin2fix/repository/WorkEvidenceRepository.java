package com.pin2fix.repository;

import com.pin2fix.model.WorkEvidence;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface WorkEvidenceRepository extends JpaRepository<WorkEvidence, Long> {
    List<WorkEvidence> findByAssignmentId(Long assignmentId);
    List<WorkEvidence> findByWorkerId(Long workerId);
}
