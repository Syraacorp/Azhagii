package com.pin2fix.dto;

import com.pin2fix.model.Role;
import lombok.Data;

@Data
public class RegisterRequest {
    private String name;
    private String email;
    private String password;
    private Role role;
    private String phone;
    private Long deptId;
    private Long govId;
    private String areaCode;
}
