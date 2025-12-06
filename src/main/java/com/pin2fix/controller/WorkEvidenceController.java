package com.pin2fix.controller;

import com.pin2fix.dto.*;
import com.pin2fix.model.*;
import com.pin2fix.service.*;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;
import java.util.List;

@RestController
@RequestMapping("/api/evidence")
@CrossOrigin(origins = "*")
@RequiredArgsConstructor
public class WorkEvidenceController {
    private final WorkEvidenceService workEvidenceService;
    private final FileStorageService fileStorageService;

    @PostMapping
    public ResponseEntity<ApiResponse<WorkEvidence>> submitEvidence(
            @RequestParam Long assignmentId,
            @RequestParam Long workerId,
            @RequestParam("file") MultipartFile file,
            @RequestParam(value = "notes", required = false) String notes) {
        try {
            String url = fileStorageService.storeFile(file, "evidence/" + assignmentId);
            WorkEvidence evidence = workEvidenceService.submitEvidence(assignmentId, workerId, url, notes);
            return ResponseEntity.ok(ApiResponse.success("Evidence submitted successfully", evidence));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/assignment/{assignmentId}")
    public ResponseEntity<ApiResponse<List<WorkEvidence>>> getEvidenceByAssignmentId(@PathVariable Long assignmentId) {
        return ResponseEntity.ok(ApiResponse.success(workEvidenceService.findByAssignmentId(assignmentId)));
    }

    @GetMapping("/worker/{workerId}")
    public ResponseEntity<ApiResponse<List<WorkEvidence>>> getEvidenceByWorkerId(@PathVariable Long workerId) {
        return ResponseEntity.ok(ApiResponse.success(workEvidenceService.findByWorkerId(workerId)));
    }
}
