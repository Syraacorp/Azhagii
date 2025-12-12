package com.pin2fix.service;

import com.pin2fix.model.GovernmentBody;
import com.pin2fix.model.Department;
import com.pin2fix.repository.GovernmentBodyRepository;
import com.pin2fix.repository.DepartmentRepository;
import org.springframework.stereotype.Service;
import java.util.List;
import java.util.Optional;

@Service
public class OrganizationService {
    private final GovernmentBodyRepository governmentBodyRepository;
    private final DepartmentRepository departmentRepository;

    public OrganizationService(GovernmentBodyRepository governmentBodyRepository, 
                               DepartmentRepository departmentRepository) {
        this.governmentBodyRepository = governmentBodyRepository;
        this.departmentRepository = departmentRepository;
    }

    // Government Body methods
    public GovernmentBody createGovBody(GovernmentBody govBody) {
        return governmentBodyRepository.save(govBody);
    }

    public Optional<GovernmentBody> findGovBodyById(Long id) {
        return governmentBodyRepository.findById(id);
    }

    public List<GovernmentBody> findAllGovBodies() {
        return governmentBodyRepository.findAll();
    }

    public void deleteGovBody(Long id) {
        governmentBodyRepository.deleteById(id);
    }

    public GovernmentBody updateGovBody(Long id, GovernmentBody updates) {
        GovernmentBody govBody = governmentBodyRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Government body not found"));
        
        if (updates.getName() != null) govBody.setName(updates.getName());
        if (updates.getJurisdiction() != null) govBody.setJurisdiction(updates.getJurisdiction());
        if (updates.getAddress() != null) govBody.setAddress(updates.getAddress());
        if (updates.getContactPhone() != null) govBody.setContactPhone(updates.getContactPhone());
        if (updates.getContactEmail() != null) govBody.setContactEmail(updates.getContactEmail());
        
        return governmentBodyRepository.save(govBody);
    }

    public GovernmentBody updateGovBodyStatus(Long id, boolean active) {
        GovernmentBody govBody = governmentBodyRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Government body not found"));
        govBody.setIsActive(active);
        return governmentBodyRepository.save(govBody);
    }

    // Department methods
    public Department createDepartment(Department department) {
        return departmentRepository.save(department);
    }

    public Optional<Department> findDepartmentById(Long id) {
        return departmentRepository.findById(id);
    }

    public List<Department> findAllDepartments() {
        return departmentRepository.findAll();
    }

    public List<Department> findDepartmentsByGovId(Long govId) {
        return departmentRepository.findByGovId(govId);
    }

    public void deleteDepartment(Long id) {
        departmentRepository.deleteById(id);
    }

    public Department updateDepartment(Long id, Department updates) {
        Department department = departmentRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Department not found"));
        
        if (updates.getName() != null) department.setName(updates.getName());
        if (updates.getDescription() != null) department.setDescription(updates.getDescription());
        if (updates.getGovId() != null) department.setGovId(updates.getGovId());
        
        return departmentRepository.save(department);
    }

    public Department updateDepartmentStatus(Long id, boolean active) {
        Department department = departmentRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Department not found"));
        department.setIsActive(active);
        return departmentRepository.save(department);
    }
}
