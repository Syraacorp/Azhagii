package com.pin2fix.repository;

import com.pin2fix.model.User;
import com.pin2fix.model.Role;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface UserRepository extends JpaRepository<User, Long> {
    Optional<User> findByEmail(String email);
    Optional<User> findByEmailAndPassword(String email, String password);
    List<User> findByRole(Role role);
    List<User> findByRoleAndDeptId(Role role, Long deptId);
    List<User> findByRoleAndGovId(Role role, Long govId);
    List<User> findByRoleAndAreaCode(Role role, String areaCode);
    boolean existsByEmail(String email);
}
