package com.pin2fix.controller;

import com.pin2fix.entity.User;
import com.pin2fix.service.UserService;
import jakarta.servlet.http.HttpSession;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import java.util.Optional;

@Controller
@RequiredArgsConstructor
public class AuthController {
    
    private final UserService userService;
    
    @GetMapping("/")
    public String index() {
        return "redirect:/login";
    }
    
    @GetMapping("/login")
    public String loginPage(HttpSession session) {
        User user = (User) session.getAttribute("user");
        if (user != null) {
            return redirectToDashboard(user);
        }
        return "login";
    }
    
    @PostMapping("/login")
    public String login(@RequestParam String email, 
                        @RequestParam String password,
                        HttpSession session,
                        RedirectAttributes redirectAttributes) {
        Optional<User> userOpt = userService.authenticate(email, password);
        
        if (userOpt.isPresent()) {
            User user = userOpt.get();
            session.setAttribute("user", user);
            session.setAttribute("userId", user.getUserId());
            session.setAttribute("userRole", user.getRole().name());
            return redirectToDashboard(user);
        } else {
            redirectAttributes.addFlashAttribute("error", "Invalid email or password");
            return "redirect:/login";
        }
    }
    
    @GetMapping("/logout")
    public String logout(HttpSession session) {
        session.invalidate();
        return "redirect:/login";
    }
    
    @GetMapping("/register")
    public String registerPage() {
        return "register";
    }
    
    @PostMapping("/register")
    public String register(@RequestParam String name,
                          @RequestParam String email,
                          @RequestParam String password,
                          @RequestParam String phone,
                          RedirectAttributes redirectAttributes) {
        if (userService.findByEmail(email).isPresent()) {
            redirectAttributes.addFlashAttribute("error", "Email already registered");
            return "redirect:/register";
        }
        
        User user = User.builder()
                .name(name)
                .email(email)
                .password(password)
                .phone(phone)
                .build();
        
        userService.save(user);
        redirectAttributes.addFlashAttribute("success", "Registration successful! Please login.");
        return "redirect:/login";
    }
    
    private String redirectToDashboard(User user) {
        return switch (user.getRole()) {
            case CITIZEN -> "redirect:/citizen/dashboard";
            case GOV_BODY -> "redirect:/gov/dashboard";
            case DEPT_HEAD -> "redirect:/dept/dashboard";
            case AREA_HEAD -> "redirect:/area/dashboard";
            case WORKER -> "redirect:/worker/dashboard";
            case ADMIN -> "redirect:/admin/dashboard";
        };
    }
}
