package com.pin2fix.controller;

import com.pin2fix.entity.*;
import com.pin2fix.service.*;
import jakarta.servlet.http.HttpSession;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@Controller
@RequestMapping("/admin")
@RequiredArgsConstructor
public class AdminController {
    
    private final IssueService issueService;
    private final GovernmentService governmentService;
    private final UserService userService;
    private final NotificationService notificationService;
    
    @GetMapping("/dashboard")
    public String dashboard(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.ADMIN) return "redirect:/login";
        
        List<Issue> allIssues = issueService.findAll();
        List<User> allUsers = userService.findAll();
        
        long pending = issueService.countByStatus(IssueStatus.PENDING);
        long inProgress = issueService.countByStatus(IssueStatus.IN_PROGRESS);
        long completed = issueService.countByStatus(IssueStatus.COMPLETED);
        
        model.addAttribute("user", user);
        model.addAttribute("issues", allIssues);
        model.addAttribute("users", allUsers);
        model.addAttribute("governmentBodies", governmentService.getAllGovernmentBodies());
        model.addAttribute("departments", governmentService.getAllDepartments());
        model.addAttribute("pendingCount", pending);
        model.addAttribute("inProgressCount", inProgress);
        model.addAttribute("completedCount", completed);
        model.addAttribute("totalUsers", allUsers.size());
        model.addAttribute("unreadNotifications", notificationService.getUnreadCount(user.getUserId()));
        
        return "admin/dashboard";
    }
    
    @GetMapping("/users")
    public String users(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.ADMIN) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("users", userService.findAll());
        
        return "admin/users";
    }
    
    @GetMapping("/issues")
    public String issues(HttpSession session, Model model) {
        User user = (User) session.getAttribute("user");
        if (user == null || user.getRole() != Role.ADMIN) return "redirect:/login";
        
        model.addAttribute("user", user);
        model.addAttribute("issues", issueService.findAll());
        
        return "admin/issues";
    }
}
