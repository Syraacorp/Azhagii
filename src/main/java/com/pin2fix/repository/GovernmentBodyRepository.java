package com.pin2fix.repository;

import com.pin2fix.entity.GovernmentBody;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface GovernmentBodyRepository extends JpaRepository<GovernmentBody, Long> {
    List<GovernmentBody> findByIsActiveTrue();
}
