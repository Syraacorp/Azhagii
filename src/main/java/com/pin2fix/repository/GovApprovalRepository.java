package com.pin2fix.repository;

import com.pin2fix.model.GovApproval;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface GovApprovalRepository extends JpaRepository<GovApproval, Long> {
    List<GovApproval> findByIssueId(Long issueId);
    Optional<GovApproval> findTopByIssueIdOrderByApprovedAtDesc(Long issueId);
    List<GovApproval> findByGovId(Long govId);
    List<GovApproval> findByApprovedBy(Long approvedBy);
}
