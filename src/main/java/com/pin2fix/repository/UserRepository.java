package com.pin2fix.repository;

import com.pin2fix.entity.User;
import com.pin2fix.entity.Role;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface UserRepository extends JpaRepository<User, Long> {
    Optional<User> findByEmail(String email);
    Optional<User> findByEmailAndPassword(String email, String password);
    List<User> findByRole(Role role);
    List<User> findByDepartmentDeptIdAndRole(Long deptId, Role role);
    List<User> findByGovernmentBodyGovIdAndRole(Long govId, Role role);
    List<User> findByDepartmentDeptIdAndAreaCodeAndRole(Long deptId, String areaCode, Role role);
    List<User> findByRoleAndIsActiveTrue(Role role);
}
