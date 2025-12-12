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
@RequestMapping("/area")
@RequiredArgsConstructor
public class AreaHeadController {
    
    private final IssueService issueService;
    private final NotificationService notificationService;
    private final UserService userService;
    
    @GetMapping("/dashboard")
    public String dashboard(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.AREA_HEAD) return "redirect:/login";
        
        List<Assignment> myAssignments = issueService.getAssignmentsForUser(user.getUserId());
        
        long assigned = myAssignments.stream().filter(a -> a.getStatus() == AssignmentStatus.ASSIGNED).count();
        long inProgress = myAssignments.stream().filter(a -> a.getStatus() == AssignmentStatus.IN_PROGRESS).count();
        long completed = myAssignments.stream().filter(a -> a.getStatus() == AssignmentStatus.COMPLETED).count();
        
        // Get workers in same department and area
        Long deptId = user.getDepartment().getDeptId();
        List<User> workers = userService.findByDepartmentAndRole(deptId, Role.WORKER);
        
        model.addAttribute("user", user);
        model.addAttribute("assignments", myAssignments);
        model.addAttribute("workers", workers);
        model.addAttribute("assignedCount", assigned);
        model.addAttribute("inProgressCount", inProgress);
        model.addAttribute("completedCount", completed);
        model.addAttribute("unreadNotifications", notificationService.getUnreadCount(user.getUserId()));
        
        return "area/dashboard";
    }
    
    @GetMapping("/issue/{id}")
    public String viewIssue(@PathVariable Long id, HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.AREA_HEAD) return "redirect:/login";
        
        Issue issue = issueService.findById(id).orElse(null);
        if (issue == null) return "redirect:/area/dashboard";
        
        Long deptId = user.getDepartment().getDeptId();
        List<User> workers = userService.findByDepartmentAndRole(deptId, Role.WORKER);
        
        model.addAttribute("user", user);
        model.addAttribute("issue", issue);
        model.addAttribute("photos", issueService.getPhotosForIssue(id));
        model.addAttribute("activityLog", issueService.getActivityLog(id));
        model.addAttribute("workers", workers);
        
        return "area/issue-detail";
    }
    
    @PostMapping("/assign-worker")
    public String assignToWorker(@RequestParam Long issueId,
                                @RequestParam Long workerId,
                                @RequestParam(required = false) String comment,
                                HttpSession session,
                                RedirectAttributes redirectAttributes) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.AREA_HEAD) return "redirect:/login";
        
        try {
            issueService.assignToWorker(issueId, workerId, user.getUserId(), comment);
            redirectAttributes.addFlashAttribute("success", "Issue assigned to Worker successfully!");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Failed to assign issue");
        }
        
        return "redirect:/area/dashboard";
    }
    
    @GetMapping("/notifications")
    public String notifications(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("notifications", notificationService.getNotificationsForUser(user.getUserId()));
        notificationService.markAllAsRead(user.getUserId());
        
        return "area/notifications";
    }
}
