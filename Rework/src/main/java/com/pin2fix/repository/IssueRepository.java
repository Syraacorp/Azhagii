package com.pin2fix.repository;

import com.pin2fix.model.Issue;
import com.pin2fix.model.IssueStatus;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface IssueRepository extends JpaRepository<Issue, Long> {
    List<Issue> findByReporterId(Long reporterId);
    List<Issue> findByStatus(IssueStatus status);
    List<Issue> findByGovId(Long govId);
    List<Issue> findByDeptId(Long deptId);
    List<Issue> findByStatusIn(List<IssueStatus> statuses);
    List<Issue> findByReporterIdAndStatus(Long reporterId, IssueStatus status);
    List<Issue> findByGovIdAndStatus(Long govId, IssueStatus status);
    List<Issue> findByDeptIdAndStatus(Long deptId, IssueStatus status);
    List<Issue> findByGovIdAndStatusIn(Long govId, List<IssueStatus> statuses);
    List<Issue> findByDeptIdAndStatusIn(Long deptId, List<IssueStatus> statuses);
    long countByStatus(IssueStatus status);
    long countByReporterId(Long reporterId);
    long countByGovId(Long govId);
    long countByDeptId(Long deptId);
}
