package com.pin2fix.controller;

import com.pin2fix.dto.*;
import com.pin2fix.model.*;
import com.pin2fix.service.*;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/organizations")
@CrossOrigin(origins = "*")
@RequiredArgsConstructor
public class OrganizationController {
    private final OrganizationService organizationService;

    // Government Body endpoints
    @PostMapping("/gov-bodies")
    public ResponseEntity<ApiResponse<GovernmentBody>> createGovBody(@RequestBody GovernmentBody govBody) {
        try {
            GovernmentBody saved = organizationService.createGovBody(govBody);
            return ResponseEntity.ok(ApiResponse.success("Government body created", saved));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/gov-bodies")
    public ResponseEntity<ApiResponse<List<GovernmentBody>>> getAllGovBodies() {
        return ResponseEntity.ok(ApiResponse.success(organizationService.findAllGovBodies()));
    }

    @GetMapping("/gov-bodies/{id}")
    public ResponseEntity<ApiResponse<GovernmentBody>> getGovBodyById(@PathVariable Long id) {
        return organizationService.findGovBodyById(id)
                .map(govBody -> ResponseEntity.ok(ApiResponse.success(govBody)))
                .orElse(ResponseEntity.notFound().build());
    }

    @DeleteMapping("/gov-bodies/{id}")
    public ResponseEntity<ApiResponse<String>> deleteGovBody(@PathVariable Long id) {
        organizationService.deleteGovBody(id);
        return ResponseEntity.ok(ApiResponse.success("Government body deleted", "OK"));
    }

    @PutMapping("/gov-bodies/{id}")
    public ResponseEntity<ApiResponse<GovernmentBody>> updateGovBody(
            @PathVariable Long id, @RequestBody GovernmentBody updates) {
        try {
            GovernmentBody updated = organizationService.updateGovBody(id, updates);
            return ResponseEntity.ok(ApiResponse.success("Government body updated", updated));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @PutMapping("/gov-bodies/{id}/status")
    public ResponseEntity<ApiResponse<GovernmentBody>> updateGovBodyStatus(
            @PathVariable Long id, @RequestParam boolean active) {
        try {
            GovernmentBody updated = organizationService.updateGovBodyStatus(id, active);
            return ResponseEntity.ok(ApiResponse.success("Status updated", updated));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    // Department endpoints
    @PostMapping("/departments")
    public ResponseEntity<ApiResponse<Department>> createDepartment(@RequestBody Department department) {
        try {
            Department saved = organizationService.createDepartment(department);
            return ResponseEntity.ok(ApiResponse.success("Department created", saved));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/departments")
    public ResponseEntity<ApiResponse<List<Department>>> getAllDepartments() {
        return ResponseEntity.ok(ApiResponse.success(organizationService.findAllDepartments()));
    }

    @GetMapping("/departments/{id}")
    public ResponseEntity<ApiResponse<Department>> getDepartmentById(@PathVariable Long id) {
        return organizationService.findDepartmentById(id)
                .map(dept -> ResponseEntity.ok(ApiResponse.success(dept)))
                .orElse(ResponseEntity.notFound().build());
    }

    @GetMapping("/departments/gov/{govId}")
    public ResponseEntity<ApiResponse<List<Department>>> getDepartmentsByGovId(@PathVariable Long govId) {
        return ResponseEntity.ok(ApiResponse.success(organizationService.findDepartmentsByGovId(govId)));
    }

    @DeleteMapping("/departments/{id}")
    public ResponseEntity<ApiResponse<String>> deleteDepartment(@PathVariable Long id) {
        organizationService.deleteDepartment(id);
        return ResponseEntity.ok(ApiResponse.success("Department deleted", "OK"));
    }

    @PutMapping("/departments/{id}")
    public ResponseEntity<ApiResponse<Department>> updateDepartment(
            @PathVariable Long id, @RequestBody Department updates) {
        try {
            Department updated = organizationService.updateDepartment(id, updates);
            return ResponseEntity.ok(ApiResponse.success("Department updated", updated));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @PutMapping("/departments/{id}/status")
    public ResponseEntity<ApiResponse<Department>> updateDepartmentStatus(
            @PathVariable Long id, @RequestParam boolean active) {
        try {
            Department updated = organizationService.updateDepartmentStatus(id, active);
            return ResponseEntity.ok(ApiResponse.success("Status updated", updated));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }
}
