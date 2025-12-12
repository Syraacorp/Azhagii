package com.pin2fix.controller;

import com.pin2fix.dto.*;
import com.pin2fix.model.*;
import com.pin2fix.service.*;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/approvals")
@CrossOrigin(origins = "*")
public class ApprovalController {
    private final ApprovalService approvalService;

    public ApprovalController(ApprovalService approvalService) {
        this.approvalService = approvalService;
    }

    @PostMapping("/head")
    public ResponseEntity<ApiResponse<HeadApproval>> submitHeadApproval(@RequestBody ApprovalRequest request) {
        try {
            HeadApproval approval = approvalService.submitHeadApproval(
                    request.getAssignmentId(),
                    request.getApprovedBy(),
                    request.getStatus(),
                    request.getComment()
            );
            return ResponseEntity.ok(ApiResponse.success("Approval submitted successfully", approval));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @PostMapping("/gov")
    public ResponseEntity<ApiResponse<GovApproval>> submitGovApproval(@RequestBody ApprovalRequest request) {
        try {
            GovApproval approval = approvalService.submitGovApproval(
                    request.getIssueId(),
                    request.getGovId(),
                    request.getApprovedBy(),
                    request.getComment()
            );
            return ResponseEntity.ok(ApiResponse.success("Government approval submitted successfully", approval));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/head/assignment/{assignmentId}")
    public ResponseEntity<ApiResponse<List<HeadApproval>>> getHeadApprovalsByAssignmentId(@PathVariable Long assignmentId) {
        return ResponseEntity.ok(ApiResponse.success(approvalService.findHeadApprovalsByAssignmentId(assignmentId)));
    }

    @GetMapping("/head/assignment/{assignmentId}/latest")
    public ResponseEntity<ApiResponse<HeadApproval>> getLatestHeadApproval(@PathVariable Long assignmentId) {
        return approvalService.findLatestHeadApproval(assignmentId)
                .map(approval -> ResponseEntity.ok(ApiResponse.success(approval)))
                .orElse(ResponseEntity.notFound().build());
    }

    @GetMapping("/gov/issue/{issueId}")
    public ResponseEntity<ApiResponse<List<GovApproval>>> getGovApprovalsByIssueId(@PathVariable Long issueId) {
        return ResponseEntity.ok(ApiResponse.success(approvalService.findGovApprovalsByIssueId(issueId)));
    }

    @GetMapping("/gov/issue/{issueId}/latest")
    public ResponseEntity<ApiResponse<GovApproval>> getLatestGovApproval(@PathVariable Long issueId) {
        return approvalService.findLatestGovApproval(issueId)
                .map(approval -> ResponseEntity.ok(ApiResponse.success(approval)))
                .orElse(ResponseEntity.notFound().build());
    }
}
