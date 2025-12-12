package com.pin2fix.controller;

import com.pin2fix.entity.*;
import com.pin2fix.service.*;
import jakarta.servlet.http.HttpSession;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import java.util.List;

@Controller
@RequestMapping("/worker")
@RequiredArgsConstructor
public class WorkerController {
    
    private final IssueService issueService;
    private final NotificationService notificationService;
    
    @GetMapping("/dashboard")
    public String dashboard(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.WORKER) return "redirect:/login";
        
        List<Assignment> myAssignments = issueService.getAssignmentsForUser(user.getUserId());
        
        long assigned = myAssignments.stream().filter(a -> a.getStatus() == AssignmentStatus.ASSIGNED).count();
        long inProgress = myAssignments.stream().filter(a -> a.getStatus() == AssignmentStatus.IN_PROGRESS).count();
        long completed = myAssignments.stream().filter(a -> a.getStatus() == AssignmentStatus.COMPLETED).count();
        
        model.addAttribute("user", user);
        model.addAttribute("assignments", myAssignments);
        model.addAttribute("assignedCount", assigned);
        model.addAttribute("inProgressCount", inProgress);
        model.addAttribute("completedCount", completed);
        model.addAttribute("unreadNotifications", notificationService.getUnreadCount(user.getUserId()));
        
        return "worker/dashboard";
    }
    
    @GetMapping("/assignment/{id}")
    public String viewAssignment(@PathVariable Long id, HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.WORKER) return "redirect:/login";
        
        List<Assignment> assignments = issueService.getAssignmentsForUser(user.getUserId());
        Assignment assignment = assignments.stream()
            .filter(a -> a.getAssignmentId().equals(id))
            .findFirst()
            .orElse(null);
        
        if (assignment == null) return "redirect:/worker/dashboard";
        
        Issue issue = assignment.getIssue();
        
        model.addAttribute("user", user);
        model.addAttribute("assignment", assignment);
        model.addAttribute("issue", issue);
        model.addAttribute("photos", issueService.getPhotosForIssue(issue.getIssueId()));
        model.addAttribute("evidence", issueService.getEvidenceForAssignment(id));
        
        return "worker/assignment-detail";
    }
    
    @PostMapping("/submit-evidence")
    public String submitEvidence(@RequestParam Long assignmentId,
                                @RequestParam String notes,
                                @RequestParam MultipartFile photo,
                                HttpSession session,
                                RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.WORKER) return "redirect:/login";
        
        try {
            issueService.submitWorkEvidence(assignmentId, user.getUserId(), notes, photo);
            redirectAttributes.addFlashAttribute("success", "Work evidence submitted successfully!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to submit evidence: " + e.getMessage());
        }
        
        return "redirect:/worker/dashboard";
    }
    
    @GetMapping("/notifications")
    public String notifications(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("notifications", notificationService.getNotificationsForUser(user.getUserId()));
        notificationService.markAllAsRead(user.getUserId());
        
        return "worker/notifications";
    }
}
