package com.pin2fix.repository;

import com.pin2fix.model.GovernmentBody;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface GovernmentBodyRepository extends JpaRepository<GovernmentBody, Long> {
}
