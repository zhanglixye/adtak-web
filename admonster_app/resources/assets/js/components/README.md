# ≪Atomic Design≫

## [Atoms]
- ***Icons***
    - JudgementIcon.vue
- ***Pickers***
    - DateTimePicker.vue
---

## [Molecules]
- ***Mails***
    - Mail.vue
    - MailWindow.vue
    - MailWindowDialog.vue
- ***Reminds***
    - Remind.vue
    - RemindDialog.vue
- ***Works***
    - Work.vue
    - ItemConfig.vue
---

## [organisms]
- ***Admin***
    - **Allocations**
        - RemindDialog.vue
        - AllocationList.vue
    - **Approvals**
        - WorkComparative.vue
    - **Businesses**
        - BusinessSearchCondition.vue
        - BusinessSearchList.vue
    - **Orders**
        - OrderSearchCondition.vue
        - OrderSearchList.vue
    - ...
- ***Biz***
    - **WFGaisansyusei**
        - ...
    - ...
- ***Common***
    - OrderDetail.vue
    - RequestContent.vue
    - ...
- **Layouts**
    - AppFooter.vue
    - AppHeader.vue
    - AppMenu.vue
    - PageHeader.vue
- ***Work***
    - **Tasks**
        - TaskSearchCondition.vue
        - TaskSearchList.vue
    - ...
---

## [Templates]
- ***Admin***
    - **Allocations**
        - SingleAllocationView.vue
        - MultiAllocationView.vue
        - MultiAllocationConfirmationView.vue
    - **Approvals**
        - ApprovalView.vue
        - ApprovalConfirmationView.vue
    - **Businesses**
        - BusinessListView.vue
        - BusinessDetailView.vue
    - **Orders**
        - OrderListView.vue
        - OrderDetailView.vue
    - ...
- ***Biz***
    - **WFGaisansyusei**
        - ...
- ***Common***
    - HomeView.vue
    - LoginView.vue
    - PasswordResetView.vue
    - UserRegistView.vue
    - WelcomeView.vue
    - ...
- ***Work***
    - **Files**
        - FileImportListView.vue
    - **Tasks**
        - TaskListView.vue
    - ...
