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
@RequestMapping("/api/issues")
@CrossOrigin(origins = "*")
@RequiredArgsConstructor
public class IssueController {
    private final IssueService issueService;
    private final FileStorageService fileStorageService;

    @PostMapping
    public ResponseEntity<ApiResponse<Issue>> createIssue(@RequestBody IssueRequest request) {
        try {
            Issue issue = Issue.builder()
                    .title(request.getTitle())
                    .description(request.getDescription())
                    .severity(request.getSeverity() != null ? request.getSeverity() : 3)
                    .latitude(request.getLatitude())
                    .longitude(request.getLongitude())
                    .addressText(request.getAddressText())
                    .reporterId(request.getReporterId())
                    .status(IssueStatus.PENDING)
                    .build();
            
            Issue savedIssue = issueService.createIssue(issue);
            return ResponseEntity.ok(ApiResponse.success("Issue created successfully", savedIssue));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @PostMapping("/{issueId}/photos")
    public ResponseEntity<ApiResponse<Photo>> uploadPhoto(
            @PathVariable Long issueId,
            @RequestParam("file") MultipartFile file,
            @RequestParam(value = "caption", required = false) String caption) {
        try {
            String url = fileStorageService.storeFile(file, "issues/" + issueId);
            Photo photo = Photo.builder()
                    .issueId(issueId)
                    .url(url)
                    .caption(caption)
                    .build();
            Photo savedPhoto = issueService.addPhoto(photo);
            return ResponseEntity.ok(ApiResponse.success("Photo uploaded successfully", savedPhoto));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping
    public ResponseEntity<ApiResponse<List<Issue>>> getAllIssues() {
        return ResponseEntity.ok(ApiResponse.success(issueService.findAll()));
    }

    @GetMapping("/{id}")
    public ResponseEntity<ApiResponse<Issue>> getIssueById(@PathVariable Long id) {
        return issueService.findById(id)
                .map(issue -> ResponseEntity.ok(ApiResponse.success(issue)))
                .orElse(ResponseEntity.notFound().build());
    }

    @GetMapping("/reporter/{reporterId}")
    public ResponseEntity<ApiResponse<List<Issue>>> getIssuesByReporter(@PathVariable Long reporterId) {
        return ResponseEntity.ok(ApiResponse.success(issueService.findByReporterId(reporterId)));
    }

    @GetMapping("/status/{status}")
    public ResponseEntity<ApiResponse<List<Issue>>> getIssuesByStatus(@PathVariable IssueStatus status) {
        return ResponseEntity.ok(ApiResponse.success(issueService.findByStatus(status)));
    }

    @GetMapping("/pending")
    public ResponseEntity<ApiResponse<List<Issue>>> getPendingIssues() {
        return ResponseEntity.ok(ApiResponse.success(issueService.findPendingIssues()));
    }

    @GetMapping("/gov/{govId}")
    public ResponseEntity<ApiResponse<List<Issue>>> getIssuesByGovId(@PathVariable Long govId) {
        return ResponseEntity.ok(ApiResponse.success(issueService.findByGovId(govId)));
    }

    @GetMapping("/department/{deptId}")
    public ResponseEntity<ApiResponse<List<Issue>>> getIssuesByDepartment(@PathVariable Long deptId) {
        return ResponseEntity.ok(ApiResponse.success(issueService.findByDeptId(deptId)));
    }

    @GetMapping("/dept/{deptId}")
    public ResponseEntity<ApiResponse<List<Issue>>> getIssuesByDeptId(@PathVariable Long deptId) {
        return ResponseEntity.ok(ApiResponse.success(issueService.findByDeptId(deptId)));
    }

    @GetMapping("/gov/{govId}/pending-approval")
    public ResponseEntity<ApiResponse<List<Issue>>> getPendingGovApproval(@PathVariable Long govId) {
        return ResponseEntity.ok(ApiResponse.success(issueService.findPendingGovApproval(govId)));
    }

    @GetMapping("/{issueId}/photos")
    public ResponseEntity<ApiResponse<List<Photo>>> getPhotosByIssueId(@PathVariable Long issueId) {
        return ResponseEntity.ok(ApiResponse.success(issueService.getPhotosByIssueId(issueId)));
    }

    @PostMapping("/forward")
    public ResponseEntity<ApiResponse<Issue>> forwardToDepartment(@RequestBody ForwardRequest request) {
        try {
            Issue issue = issueService.forwardToDepartment(
                    request.getIssueId(),
                    request.getDeptId(),
                    request.getGovId(),
                    request.getActorId()
            );
            return ResponseEntity.ok(ApiResponse.success("Issue forwarded to department", issue));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @PutMapping("/{issueId}/status")
    public ResponseEntity<ApiResponse<Issue>> updateStatus(
            @PathVariable Long issueId,
            @RequestParam IssueStatus status,
            @RequestParam Long actorId) {
        try {
            Issue issue = issueService.updateStatus(issueId, status, actorId);
            return ResponseEntity.ok(ApiResponse.success("Status updated", issue));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/stats")
    public ResponseEntity<ApiResponse<Object>> getStats() {
        return ResponseEntity.ok(ApiResponse.success(new Object() {
            public final long total = issueService.countAll();
            public final long pending = issueService.countByStatus(IssueStatus.PENDING);
            public final long inProgress = issueService.countByStatus(IssueStatus.IN_PROGRESS);
            public final long completed = issueService.countByStatus(IssueStatus.COMPLETED);
            public final long reopened = issueService.countByStatus(IssueStatus.REOPENED);
        }));
    }
}
