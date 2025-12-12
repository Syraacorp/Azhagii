package com.pin2fix.repository;

import com.pin2fix.entity.Issue;
import com.pin2fix.entity.IssueStatus;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface IssueRepository extends JpaRepository<Issue, Long> {
    List<Issue> findByReporterUserId(Long userId);
    List<Issue> findByGovernmentBodyGovId(Long govId);
    List<Issue> findByDepartmentDeptId(Long deptId);
    List<Issue> findByStatus(IssueStatus status);
    List<Issue> findByGovernmentBodyGovIdAndStatus(Long govId, IssueStatus status);
    List<Issue> findByDepartmentDeptIdAndStatus(Long deptId, IssueStatus status);
    
    @Query("SELECT i FROM Issue i WHERE i.governmentBody.govId = :govId ORDER BY i.createdAt DESC")
    List<Issue> findAllByGovIdOrderByCreatedAtDesc(@Param("govId") Long govId);
    
    @Query("SELECT i FROM Issue i WHERE i.department.deptId = :deptId ORDER BY i.createdAt DESC")
    List<Issue> findAllByDeptIdOrderByCreatedAtDesc(@Param("deptId") Long deptId);
    
    @Query("SELECT COUNT(i) FROM Issue i WHERE i.status = :status")
    Long countByStatus(@Param("status") IssueStatus status);
    
    @Query("SELECT COUNT(i) FROM Issue i WHERE i.governmentBody.govId = :govId")
    Long countByGovId(@Param("govId") Long govId);
    
    List<Issue> findByStatusIn(List<IssueStatus> statuses);
    
    List<Issue> findByGovernmentBodyGovIdAndStatusIn(Long govId, List<IssueStatus> statuses);
}
