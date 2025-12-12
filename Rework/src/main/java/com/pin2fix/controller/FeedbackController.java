package com.pin2fix.controller;

import com.pin2fix.dto.*;
import com.pin2fix.model.*;
import com.pin2fix.service.*;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/feedback")
@CrossOrigin(origins = "*")
public class FeedbackController {
    private final FeedbackService feedbackService;

    public FeedbackController(FeedbackService feedbackService) {
        this.feedbackService = feedbackService;
    }

    @GetMapping
    public ResponseEntity<ApiResponse<List<Feedback>>> getAllFeedback() {
        return ResponseEntity.ok(ApiResponse.success(feedbackService.findAll()));
    }

    @PostMapping
    public ResponseEntity<ApiResponse<Feedback>> submitFeedback(@RequestBody FeedbackRequest request) {
        try {
            Feedback feedback = feedbackService.submitFeedback(
                    request.getIssueId(),
                    request.getUserId(),
                    request.getRating(),
                    request.getMessage(),
                    request.getIsPositive()
            );
            return ResponseEntity.ok(ApiResponse.success("Feedback submitted successfully", feedback));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(ApiResponse.error(e.getMessage()));
        }
    }

    @GetMapping("/issue/{issueId}")
    public ResponseEntity<ApiResponse<List<Feedback>>> getFeedbackByIssueId(@PathVariable Long issueId) {
        return ResponseEntity.ok(ApiResponse.success(feedbackService.findByIssueId(issueId)));
    }

    @GetMapping("/user/{userId}")
    public ResponseEntity<ApiResponse<List<Feedback>>> getFeedbackByUserId(@PathVariable Long userId) {
        return ResponseEntity.ok(ApiResponse.success(feedbackService.findByUserId(userId)));
    }

    @GetMapping("/issue/{issueId}/user/{userId}")
    public ResponseEntity<ApiResponse<Feedback>> getFeedbackByIssueAndUser(
            @PathVariable Long issueId, @PathVariable Long userId) {
        return feedbackService.findByIssueIdAndUserId(issueId, userId)
                .map(feedback -> ResponseEntity.ok(ApiResponse.success(feedback)))
                .orElse(ResponseEntity.notFound().build());
    }

    @GetMapping("/issue/{issueId}/user/{userId}/exists")
    public ResponseEntity<ApiResponse<Boolean>> hasFeedback(
            @PathVariable Long issueId, @PathVariable Long userId) {
        return ResponseEntity.ok(ApiResponse.success(feedbackService.hasFeedback(issueId, userId)));
    }
}
