package com.pin2fix.controller;

import com.pin2fix.entity.*;
import com.pin2fix.service.*;
import jakarta.servlet.http.HttpSession;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import java.util.*;

@Controller
@RequestMapping("/gov")
@RequiredArgsConstructor
public class GovBodyController {
    
    private final IssueService issueService;
    private final GovernmentService governmentService;
    private final NotificationService notificationService;
    private final UserService userService;
    
    @GetMapping("/dashboard")
    public String dashboard(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.GOV_BODY) return "redirect:/login";
        
        Long govId = user.getGovernmentBody().getGovId();
        List<Issue> allIssues = issueService.findByGovId(govId);
        List<Issue> pendingIssues = issueService.findPendingForGov(govId);
        
        long pending = allIssues.stream().filter(i -> i.getStatus() == IssueStatus.PENDING).count();
        long forwarded = allIssues.stream().filter(i -> i.getStatus() == IssueStatus.FORWARDED).count();
        long headApproved = allIssues.stream().filter(i -> i.getStatus() == IssueStatus.HEAD_APPROVED).count();
        long completed = allIssues.stream().filter(i -> i.getStatus() == IssueStatus.COMPLETED).count();
        
        // Get first photo for each pending issue
        Map<Long, String> issuePhotos = new HashMap<>();
        for (Issue issue : pendingIssues) {
            List<Photo> photos = issueService.getPhotosForIssue(issue.getIssueId());
            if (photos != null && !photos.isEmpty()) {
                issuePhotos.put(issue.getIssueId(), photos.get(0).getUrl());
            }
        }
        
        model.addAttribute("user", user);
        model.addAttribute("issues", allIssues);
        model.addAttribute("pendingIssues", pendingIssues);
        model.addAttribute("issuePhotos", issuePhotos);
        model.addAttribute("departments", governmentService.getDepartmentsByGov(govId));
        model.addAttribute("pendingCount", pending);
        model.addAttribute("forwardedCount", forwarded);
        model.addAttribute("headApprovedCount", headApproved);
        model.addAttribute("completedCount", completed);
        model.addAttribute("unreadNotifications", notificationService.getUnreadCount(user.getUserId()));
        
        return "gov/dashboard";
    }
    
    @GetMapping("/issue/{id}")
    public String viewIssue(@PathVariable Long id, HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.GOV_BODY) return "redirect:/login";
        
        Issue issue = issueService.findById(id).orElse(null);
        if (issue == null) return "redirect:/gov/dashboard";
        
        Long govId = user.getGovernmentBody().getGovId();
        
        model.addAttribute("user", user);
        model.addAttribute("issue", issue);
        model.addAttribute("photos", issueService.getPhotosForIssue(id));
        model.addAttribute("activityLog", issueService.getActivityLog(id));
        model.addAttribute("departments", governmentService.getDepartmentsByGov(govId));
        
        return "gov/issue-detail";
    }
    
    @PostMapping("/forward")
    public String forwardToDepartment(@RequestParam Long issueId,
                                      @RequestParam Long deptId,
                                      HttpSession session,
                                      RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.GOV_BODY) return "redirect:/login";
        
        try {
            issueService.forwardToDepartment(issueId, deptId, user.getUserId());
            redirectAttributes.addFlashAttribute("success", "Issue forwarded to department successfully!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to forward issue");
        }
        
        return "redirect:/gov/dashboard";
    }
    
    @PostMapping("/approve")
    public String approveIssue(@RequestParam Long issueId,
                              @RequestParam String status,
                              @RequestParam String comment,
                              HttpSession session,
                              RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.GOV_BODY) return "redirect:/login";
        
        try {
            ApprovalStatus approvalStatus = "APPROVED".equals(status) ? 
                ApprovalStatus.APPROVED : ApprovalStatus.REJECTED;
            issueService.approveByGov(issueId, user.getUserId(), approvalStatus, comment);
            redirectAttributes.addFlashAttribute("success", "Issue " + status.toLowerCase() + " successfully!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to process approval");
        }
        
        return "redirect:/gov/dashboard";
    }
    
    @GetMapping("/notifications")
    public String notifications(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("notifications", notificationService.getNotificationsForUser(user.getUserId()));
        notificationService.markAllAsRead(user.getUserId());
        
        return "gov/notifications";
    }
}
