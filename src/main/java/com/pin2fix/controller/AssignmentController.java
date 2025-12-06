package com.pin2fix.controller;

import com.pin2fix.dto.*;
import com.pin2fix.model.*;
import com.pin2fix.service.*;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/assignments")
@CrossOrigin(origins = "*")
@RequiredArgsConstructor
public class AssignmentController {
    private final AssignmentService assignmentService;

    @PostMapping
    public ResponseEntity<ApiResponse<Assignment>> createAssignment(@RequestBody AssignmentRequest request) {
        try {
            Assignment assignment = assignmentService.createAssignment(
                    request.getIssueId(),
                    request.getAssignedBy(),
                    request.getAssigneeId(),
                    request.getRoleAssignee(),
                    request.getComment()
            );
            return ResponseEntity.ok(ApiResponse.success("Assignment created successfully", assignment));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/{id}")
    public ResponseEntity<ApiResponse<Assignment>> getAssignmentById(@PathVariable Long id) {
        return assignmentService.findById(id)
                .map(assignment -> ResponseEntity.ok(ApiResponse.success(assignment)))
                .orElse(ResponseEntity.notFound().build());
    }

    @GetMapping("/issue/{issueId}")
    public ResponseEntity<ApiResponse<List<Assignment>>> getAssignmentsByIssueId(@PathVariable Long issueId) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.findByIssueId(issueId)));
    }

    @GetMapping("/assignee/{assigneeId}")
    public ResponseEntity<ApiResponse<List<Assignment>>> getAssignmentsByAssigneeId(@PathVariable Long assigneeId) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.findByAssigneeId(assigneeId)));
    }

    @GetMapping("/assignee/{assigneeId}/status/{status}")
    public ResponseEntity<ApiResponse<List<Assignment>>> getAssignmentsByAssigneeAndStatus(
            @PathVariable Long assigneeId, @PathVariable AssignmentStatus status) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.findByAssigneeIdAndStatus(assigneeId, status)));
    }

    @GetMapping("/issue/{issueId}/latest")
    public ResponseEntity<ApiResponse<Assignment>> getLatestAssignment(@PathVariable Long issueId) {
        return assignmentService.findLatestByIssueId(issueId)
                .map(assignment -> ResponseEntity.ok(ApiResponse.success(assignment)))
                .orElse(ResponseEntity.notFound().build());
    }

    @PutMapping("/{assignmentId}/status")
    public ResponseEntity<ApiResponse<Assignment>> updateStatus(
            @PathVariable Long assignmentId,
            @RequestParam AssignmentStatus status,
            @RequestParam Long actorId) {
        try {
            Assignment assignment = assignmentService.updateStatus(assignmentId, status, actorId);
            return ResponseEntity.ok(ApiResponse.success("Status updated", assignment));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/assignee/{assigneeId}/stats")
    public ResponseEntity<ApiResponse<Object>> getAssigneeStats(@PathVariable Long assigneeId) {
        return ResponseEntity.ok(ApiResponse.success(new Object() {
            public final long total = assignmentService.countByAssigneeId(assigneeId);
            public final long assigned = assignmentService.countByAssigneeIdAndStatus(assigneeId, AssignmentStatus.ASSIGNED);
            public final long inProgress = assignmentService.countByAssigneeIdAndStatus(assigneeId, AssignmentStatus.IN_PROGRESS);
            public final long completed = assignmentService.countByAssigneeIdAndStatus(assigneeId, AssignmentStatus.COMPLETED);
            public final long reopened = assignmentService.countByAssigneeIdAndStatus(assigneeId, AssignmentStatus.REOPENED);
        }));
    }
}
