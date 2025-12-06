package com.pin2fix.controller;

import com.pin2fix.dto.*;
import com.pin2fix.model.User;
import com.pin2fix.model.Role;
import com.pin2fix.service.UserService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/auth")
@CrossOrigin(origins = "*")
public class AuthController {
    private final UserService userService;

    public AuthController(UserService userService) {
        this.userService = userService;
    }

    @PostMapping("/register")
    public ResponseEntity<ApiResponse<User>> register(@RequestBody RegisterRequest request) {
        try {
            User user = User.builder()
                    .name(request.getName())
                    .email(request.getEmail())
                    .password(request.getPassword())
                    .role(request.getRole() != null ? request.getRole() : Role.CITIZEN)
                    .phone(request.getPhone())
                    .deptId(request.getDeptId())
                    .govId(request.getGovId())
                    .areaCode(request.getAreaCode())
                    .build();
            
            User savedUser = userService.register(user);
            return ResponseEntity.ok(ApiResponse.success("Registration successful", savedUser));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @PostMapping("/login")
    public ResponseEntity<ApiResponse<User>> login(@RequestBody LoginRequest request) {
        return userService.login(request.getEmail(), request.getPassword())
                .map(user -> ResponseEntity.ok(ApiResponse.success("Login successful", user)))
                .orElse(ResponseEntity.badRequest().body(ApiResponse.error("Invalid email or password")));
    }

    @GetMapping("/users")
    public ResponseEntity<ApiResponse<List<User>>> getAllUsers() {
        return ResponseEntity.ok(ApiResponse.success(userService.findAll()));
    }

    @GetMapping("/users/{id}")
    public ResponseEntity<ApiResponse<User>> getUserById(@PathVariable Long id) {
        return userService.findById(id)
                .map(user -> ResponseEntity.ok(ApiResponse.success(user)))
                .orElse(ResponseEntity.notFound().build());
    }

    @GetMapping("/users/role/{role}")
    public ResponseEntity<ApiResponse<List<User>>> getUsersByRole(@PathVariable Role role) {
        return ResponseEntity.ok(ApiResponse.success(userService.findByRole(role)));
    }

    @GetMapping("/users/dept/{deptId}/role/{role}")
    public ResponseEntity<ApiResponse<List<User>>> getUsersByRoleAndDept(
            @PathVariable Long deptId, @PathVariable Role role) {
        return ResponseEntity.ok(ApiResponse.success(userService.findByRoleAndDeptId(role, deptId)));
    }

    @GetMapping("/users/gov/{govId}/role/{role}")
    public ResponseEntity<ApiResponse<List<User>>> getUsersByRoleAndGov(
            @PathVariable Long govId, @PathVariable Role role) {
        return ResponseEntity.ok(ApiResponse.success(userService.findByRoleAndGovId(role, govId)));
    }
}
