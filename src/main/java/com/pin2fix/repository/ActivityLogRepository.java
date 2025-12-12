package com.pin2fix.repository;

import com.pin2fix.entity.ActivityLog;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface ActivityLogRepository extends JpaRepository<ActivityLog, Long> {
    List<ActivityLog> findByIssueIssueIdOrderByCreatedAtDesc(Long issueId);
    List<ActivityLog> findByActorUserIdOrderByCreatedAtDesc(Long userId);
}
