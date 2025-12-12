package com.pin2fix.service;

import com.pin2fix.entity.*;
import com.pin2fix.repository.*;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;
import java.util.Optional;

@Service
@RequiredArgsConstructor
public class UserService {
    
    private final UserRepository userRepository;
    
    public Optional<User> authenticate(String email, String password) {
        return userRepository.findByEmailAndPassword(email, password);
    }
    
    public Optional<User> findByEmail(String email) {
        return userRepository.findByEmail(email);
    }
    
    public Optional<User> findById(Long id) {
        return userRepository.findById(id);
    }
    
    public List<User> findByRole(Role role) {
        return userRepository.findByRole(role);
    }
    
    public List<User> findByDepartmentAndRole(Long deptId, Role role) {
        return userRepository.findByDepartmentDeptIdAndRole(deptId, role);
    }
    
    public List<User> findByGovAndRole(Long govId, Role role) {
        return userRepository.findByGovernmentBodyGovIdAndRole(govId, role);
    }
    
    public List<User> findWorkersByDeptAndArea(Long deptId, String areaCode) {
        return userRepository.findByDepartmentDeptIdAndAreaCodeAndRole(deptId, areaCode, Role.WORKER);
    }
    
    @Transactional
    public User save(User user) {
        return userRepository.save(user);
    }
    
    public List<User> findAll() {
        return userRepository.findAll();
    }
}
