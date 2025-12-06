package com.pin2fix.model;

public enum IssueStatus {
    PENDING,                              // Initial state when citizen reports
    TRIAGED,                              // Forwarded to department by gov body
    ASSIGNED,                             // Assigned to area head/worker
    IN_PROGRESS,                          // Worker started working
    EVIDENCE_SUBMITTED,                   // Worker submitted evidence  
    WORK_COMPLETED_PENDING_HEAD_APPROVAL, // Work done, awaiting head approval
    PENDING_GOV_APPROVAL,                 // Head approved, awaiting gov body final approval
    COMPLETED,                            // Gov body gave final approval
    REOPENED,                             // Negative feedback, needs rework
    REJECTED                              // Issue rejected
}
