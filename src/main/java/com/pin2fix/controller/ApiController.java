package com.pin2fix.controller;

import com.pin2fix.entity.Department;
import com.pin2fix.service.GovernmentService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api")
@RequiredArgsConstructor
public class ApiController {
    
    private final GovernmentService governmentService;
    
    @GetMapping("/departments/{govId}")
    public ResponseEntity<List<DepartmentDTO>> getDepartmentsByGov(@PathVariable Long govId) {
        List<Department> departments = governmentService.getDepartmentsByGov(govId);
        List<DepartmentDTO> dtos = departments.stream()
            .map(d -> new DepartmentDTO(d.getDeptId(), d.getName()))
            .collect(Collectors.toList());
        return ResponseEntity.ok(dtos);
    }
    
    record DepartmentDTO(Long id, String name) {}
}
