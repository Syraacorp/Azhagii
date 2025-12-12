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

import java.math.BigDecimal;
import java.util.List;

@Controller
@RequestMapping("/citizen")
@RequiredArgsConstructor
public class CitizenController {
    
    private final IssueService issueService;
    private final GovernmentService governmentService;
    private final NotificationService notificationService;
    private final UserService userService;
    
    @GetMapping("/dashboard")
    public String dashboard(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        List<Issue> myIssues = issueService.findByReporter(user.getUserId());
        Long unreadNotifications = notificationService.getUnreadCount(user.getUserId());
        
        long pending = myIssues.stream().filter(i -> i.getStatus() == IssueStatus.PENDING).count();
        long inProgress = myIssues.stream().filter(i -> 
            i.getStatus() == IssueStatus.FORWARDED || 
            i.getStatus() == IssueStatus.ASSIGNED || 
            i.getStatus() == IssueStatus.IN_PROGRESS ||
            i.getStatus() == IssueStatus.EVIDENCE_SUBMITTED ||
            i.getStatus() == IssueStatus.HEAD_APPROVED).count();
        long completed = myIssues.stream().filter(i -> i.getStatus() == IssueStatus.COMPLETED).count();
        
        model.addAttribute("user", user);
        model.addAttribute("issues", myIssues);
        model.addAttribute("pendingCount", pending);
        model.addAttribute("inProgressCount", inProgress);
        model.addAttribute("completedCount", completed);
        model.addAttribute("unreadNotifications", unreadNotifications);
        
        return "citizen/dashboard";
    }
    
    @GetMapping("/report")
    public String reportIssuePage(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("governmentBodies", governmentService.getAllGovernmentBodies());
        
        return "citizen/report";
    }
    
    @PostMapping("/report")
    public String reportIssue(@RequestParam String title,
                             @RequestParam String description,
                             @RequestParam Integer severity,
                             @RequestParam BigDecimal latitude,
                             @RequestParam BigDecimal longitude,
                             @RequestParam String addressText,
                             @RequestParam Long govId,
                             @RequestParam(required = false) MultipartFile[] photos,
                             HttpSession session,
                             RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        try {
            issueService.createIssue(title, description, severity, latitude, longitude, 
                addressText, user.getUserId(), govId, photos);
            redirectAttributes.addFlashAttribute("success", "Issue reported successfully!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to report issue: " + e.getMessage());
        }
        
        return "redirect:/citizen/dashboard";
    }
    
    @GetMapping("/issue/{id}")
    public String viewIssue(@PathVariable Long id, HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        Issue issue = issueService.findById(id).orElse(null);
        if (issue == null) return "redirect:/citizen/dashboard";
        
        model.addAttribute("user", user);
        model.addAttribute("issue", issue);
        model.addAttribute("photos", issueService.getPhotosForIssue(id));
        model.addAttribute("activityLog", issueService.getActivityLog(id));
        model.addAttribute("feedback", issueService.getFeedbackForIssue(id).orElse(null));
        
        return "citizen/issue-detail";
    }
    
    @PostMapping("/feedback")
    public String submitFeedback(@RequestParam Long issueId,
                                @RequestParam Integer rating,
                                @RequestParam String message,
                                HttpSession session,
                                RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        try {
            issueService.submitFeedback(issueId, user.getUserId(), rating, message);
            redirectAttributes.addFlashAttribute("success", "Thank you for your feedback!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to submit feedback");
        }
        
        return "redirect:/citizen/issue/" + issueId;
    }
    
    @GetMapping("/notifications")
    public String notifications(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("notifications", notificationService.getNotificationsForUser(user.getUserId()));
        
        notificationService.markAllAsRead(user.getUserId());
        
        return "citizen/notifications";
    }
}
