package com.pin2fix.controller;

import com.pin2fix.entity.*;
import com.pin2fix.service.*;
import jakarta.servlet.http.HttpSession;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import java.util.List;

@Controller
@RequestMapping("/dept")
@RequiredArgsConstructor
public class DeptHeadController {
    
    private final IssueService issueService;
    private final GovernmentService governmentService;
    private final NotificationService notificationService;
    private final UserService userService;
    
    @GetMapping("/dashboard")
    public String dashboard(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.DEPT_HEAD) return "redirect:/login";
        
        Long deptId = user.getDepartment().getDeptId();
        List<Issue> deptIssues = issueService.findByDeptId(deptId);
        List<Issue> forwardedIssues = issueService.findForwardedForDept(deptId);
        List<Issue> evidenceSubmitted = issueService.findEvidenceSubmittedForHead(deptId);
        
        long forwarded = deptIssues.stream().filter(i -> i.getStatus() == IssueStatus.FORWARDED).count();
        long assigned = deptIssues.stream().filter(i -> i.getStatus() == IssueStatus.ASSIGNED).count();
        long inProgress = deptIssues.stream().filter(i -> i.getStatus() == IssueStatus.IN_PROGRESS).count();
        long evidenceCount = deptIssues.stream().filter(i -> i.getStatus() == IssueStatus.EVIDENCE_SUBMITTED).count();
        
        // Get area heads for assignment
        List<User> areaHeads = userService.findByDepartmentAndRole(deptId, Role.AREA_HEAD);
        
        model.addAttribute("user", user);
        model.addAttribute("issues", deptIssues);
        model.addAttribute("forwardedIssues", forwardedIssues);
        model.addAttribute("evidenceSubmitted", evidenceSubmitted);
        model.addAttribute("areaHeads", areaHeads);
        model.addAttribute("forwardedCount", forwarded);
        model.addAttribute("assignedCount", assigned);
        model.addAttribute("inProgressCount", inProgress);
        model.addAttribute("evidenceCount", evidenceCount);
        model.addAttribute("unreadNotifications", notificationService.getUnreadCount(user.getUserId()));
        
        return "dept/dashboard";
    }
    
    @GetMapping("/issue/{id}")
    public String viewIssue(@PathVariable Long id, HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.DEPT_HEAD) return "redirect:/login";
        
        Issue issue = issueService.findById(id).orElse(null);
        if (issue == null) return "redirect:/dept/dashboard";
        
        Long deptId = user.getDepartment().getDeptId();
        List<User> areaHeads = userService.findByDepartmentAndRole(deptId, Role.AREA_HEAD);
        
        model.addAttribute("user", user);
        model.addAttribute("issue", issue);
        model.addAttribute("photos", issueService.getPhotosForIssue(id));
        model.addAttribute("activityLog", issueService.getActivityLog(id));
        model.addAttribute("areaHeads", areaHeads);
        
        return "dept/issue-detail";
    }
    
    @PostMapping("/assign")
    public String assignToAreaHead(@RequestParam Long issueId,
                                   @RequestParam Long areaHeadId,
                                   @RequestParam(required = false) String comment,
                                   HttpSession session,
                                   RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.DEPT_HEAD) return "redirect:/login";
        
        try {
            issueService.assignToAreaHead(issueId, areaHeadId, user.getUserId(), comment);
            redirectAttributes.addFlashAttribute("success", "Issue assigned to Area Head successfully!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to assign issue");
        }
        
        return "redirect:/dept/dashboard";
    }
    
    @PostMapping("/approve")
    public String approveEvidence(@RequestParam Long issueId,
                                 @RequestParam String status,
                                 @RequestParam String comment,
                                 HttpSession session,
                                 RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.DEPT_HEAD) return "redirect:/login";
        
        try {
            ApprovalStatus approvalStatus = "APPROVED".equals(status) ? 
                ApprovalStatus.APPROVED : ApprovalStatus.REJECTED;
            issueService.approveByHead(issueId, user.getUserId(), approvalStatus, comment);
            redirectAttributes.addFlashAttribute("success", "Evidence " + status.toLowerCase() + " successfully!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to process approval");
        }
        
        return "redirect:/dept/dashboard";
    }
    
    @GetMapping("/notifications")
    public String notifications(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("notifications", notificationService.getNotificationsForUser(user.getUserId()));
        notificationService.markAllAsRead(user.getUserId());
        
        return "dept/notifications";
    }
}
