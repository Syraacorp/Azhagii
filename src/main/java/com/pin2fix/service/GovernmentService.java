package com.pin2fix.service;

import com.pin2fix.entity.GovernmentBody;
import com.pin2fix.entity.Department;
import com.pin2fix.repository.GovernmentBodyRepository;
import com.pin2fix.repository.DepartmentRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.util.List;
import java.util.Optional;

@Service
@RequiredArgsConstructor
public class GovernmentService {
    
    private final GovernmentBodyRepository governmentBodyRepository;
    private final DepartmentRepository departmentRepository;
    
    public List<GovernmentBody> getAllGovernmentBodies() {
        return governmentBodyRepository.findByIsActiveTrue();
    }
    
    public Optional<GovernmentBody> getGovernmentBody(Long id) {
        return governmentBodyRepository.findById(id);
    }
    
    public List<Department> getDepartmentsByGov(Long govId) {
        return departmentRepository.findByGovernmentBodyGovIdAndIsActiveTrue(govId);
    }
    
    public Optional<Department> getDepartment(Long id) {
        return departmentRepository.findById(id);
    }
    
    public List<Department> getAllDepartments() {
        return departmentRepository.findByIsActiveTrue();
    }
}
