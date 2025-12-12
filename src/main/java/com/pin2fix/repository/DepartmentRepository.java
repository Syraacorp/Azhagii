package com.pin2fix.repository;

import com.pin2fix.entity.Department;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface DepartmentRepository extends JpaRepository<Department, Long> {
    List<Department> findByGovernmentBodyGovIdAndIsActiveTrue(Long govId);
    List<Department> findByIsActiveTrue();
}
