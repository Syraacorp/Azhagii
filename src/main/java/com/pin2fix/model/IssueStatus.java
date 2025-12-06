package com.pin2fix.model;

public enum IssueStatus {
    PENDING,                              // Initial state when citizen reports
    FORWARDED,                            // Forwarded to department by gov body
    ASSIGNED,                             // Assigned to worker by dept head
    IN_PROGRESS,                          // Worker started working
    EVIDENCE_SUBMITTED,                   // Worker submitted evidence
    WORK_COMPLETED_PENDING_HEAD_APPROVAL, // Work done, awaiting head approval
    HEAD_APPROVED,                        // Department head approved
    COMPLETED,                            // Gov body gave final approval
    REOPENED,                             // Negative feedback, needs rework
    REJECTED                              // Issue rejected
}
