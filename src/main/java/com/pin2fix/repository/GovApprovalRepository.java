package com.pin2fix.repository;

import com.pin2fix.entity.GovApproval;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface GovApprovalRepository extends JpaRepository<GovApproval, Long> {
    List<GovApproval> findByIssueIssueId(Long issueId);
    Optional<GovApproval> findTopByIssueIssueIdOrderByApprovedAtDesc(Long issueId);
}
