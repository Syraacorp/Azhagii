package com.pin2fix.service;

import com.pin2fix.model.User;
import com.pin2fix.model.Role;
import com.pin2fix.repository.UserRepository;
import org.springframework.stereotype.Service;
import java.util.List;
import java.util.Optional;

@Service
public class UserService {
    private final UserRepository userRepository;

    public UserService(UserRepository userRepository) {
        this.userRepository = userRepository;
    }

    public User register(User user) {
        if (userRepository.existsByEmail(user.getEmail())) {
            throw new RuntimeException("Email already exists");
        }
        return userRepository.save(user);
    }

    public Optional<User> login(String email, String password) {
        return userRepository.findByEmailAndPassword(email, password);
    }

    public Optional<User> findById(Long id) {
        return userRepository.findById(id);
    }

    public Optional<User> findByEmail(String email) {
        return userRepository.findByEmail(email);
    }

    public List<User> findByRole(Role role) {
        return userRepository.findByRole(role);
    }

    public List<User> findByRoleAndDeptId(Role role, Long deptId) {
        return userRepository.findByRoleAndDeptId(role, deptId);
    }

    public List<User> findByRoleAndGovId(Role role, Long govId) {
        return userRepository.findByRoleAndGovId(role, govId);
    }

    public List<User> findAll() {
        return userRepository.findAll();
    }

    public User save(User user) {
        return userRepository.save(user);
    }

    public User updateUser(Long id, User userUpdates) {
        User user = userRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("User not found"));
        
        if (userUpdates.getName() != null) user.setName(userUpdates.getName());
        if (userUpdates.getEmail() != null) user.setEmail(userUpdates.getEmail());
        if (userUpdates.getPhone() != null) user.setPhone(userUpdates.getPhone());
        if (userUpdates.getRole() != null) user.setRole(userUpdates.getRole());
        if (userUpdates.getDeptId() != null) user.setDeptId(userUpdates.getDeptId());
        if (userUpdates.getGovId() != null) user.setGovId(userUpdates.getGovId());
        
        return userRepository.save(user);
    }

    public User updateStatus(Long id, boolean active) {
        User user = userRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("User not found"));
        user.setIsActive(active);
        return userRepository.save(user);
    }

    public void deleteUser(Long id) {
        userRepository.deleteById(id);
    }

    public void deleteById(Long id) {
        userRepository.deleteById(id);
    }
}
