package com.pin2fix.repository;

import com.pin2fix.entity.Notification;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface NotificationRepository extends JpaRepository<Notification, Long> {
    List<Notification> findByUserUserIdOrderByCreatedAtDesc(Long userId);
    List<Notification> findByUserUserIdAndIsReadFalseOrderByCreatedAtDesc(Long userId);
    Long countByUserUserIdAndIsReadFalse(Long userId);
}
