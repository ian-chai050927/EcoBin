ECOBIN COMPLETE XAMPP SETUP
===========================

This build is designed around the lecturer feedback and the rubric:
- PHP + MySQL
- MVC
- ORM: Doctrine ORM (real ORM library)
- Design Pattern: Observer Pattern + MVC
- Secure Coding
- Web Service exposure + consumption + bidirectional module communication
- IFA envelope: requestID, timestamp, service, payload, status

1) REQUIREMENTS
---------------
- XAMPP Apache + MySQL running
- PHP 8.0+
- Composer installed

Check:
  C:\xampp\php\php.exe -v
  composer --version

2) COPY PROJECT
---------------
Copy this project into:
  C:\xampp\htdocs\ECOBIN

If you already have an ECOBIN folder, back it up first.

3) INSTALL COMPOSER PACKAGES
----------------------------
Open CMD in:
  C:\xampp\htdocs\ECOBIN

Run:
  composer install

If "composer is not recognized", install Composer for Windows and point it to:
  C:\xampp\php\php.exe

4) DATABASE
-----------
Open:
  http://localhost/phpmyadmin

Import:
  database/ecobin.sql

The database connection is:
  config/database.php

Default XAMPP:
  user=root
  password=blank
  port=3306

5) BASE URL
-----------
Open:
  config/app.php

If your folder name is ECOBIN, keep:
  'base_url' => 'http://localhost/ECOBIN'

Change the service_token before final submission.

6) RUN
------
Open:
  http://localhost/ECOBIN/

7) DEMO ACCOUNTS
----------------
All seeded accounts use:
  Password123!

Resident:
  resident@ecobin.test

Admin:
  admin@ecobin.test

Collection Staff:
  collector@ecobin.test

Recycling Center Operator:
  operator@ecobin.test

8) LECTURER FEEDBACK IMPLEMENTED
--------------------------------
MODULE 1
- Password HASHING via password_hash/password_verify.
- Module 1 owns account/profile/status logic.
- Module 5 does NOT duplicate user management.

MODULE 2
- Owns collection assignment.
- Owns collection-status workflow.
- Module 5 only receives/delivers notifications for these events.

MODULE 3
- Recycling Centre Operator has real responsibilities:
  * maintain centre data
  * update availability
  * review recycling submissions
  * approve/reject and award points
  * confirm/complete/cancel appointments

MODULE 4
- Focused on dashboard analytics and reports.
- It reads statistics and can analyse data.
- Logging capture is NOT duplicated here.

MODULE 5
- Owns notification creation/delivery.
- Announcements.
- System configuration.
- Audit logs.
- Activity logs.
- No user/account CRUD.

9) RUBRIC EVIDENCE
------------------
PHP WEB APPLICATION
- Forms and sessions: all modules
- DB access: Doctrine EntityManager
- MVC: src/Controllers + src/Entities + views
- Maintainability: PSR-4 Composer autoload, services, observers, helpers

DESIGN PATTERN
Primary explicit pattern: OBSERVER.
Structure:
  EventDispatcher (Subject)
       |
       +--> NotificationObserver
       |
       +--> AuditObserver

Real problem solved:
When collection/recycling/account events happen, modules should not duplicate notification
and logging logic. Controllers dispatch one domain event; observers handle notification and audit
responsibilities independently.

Mermaid class diagram:
classDiagram
  class EventDispatcher {
    -observers
    +attach(EventObserver)
    +dispatch(event,data)
  }
  class EventObserver {
    <<interface>>
    +update(event,data)
  }
  class NotificationObserver {
    +update(event,data)
  }
  class AuditObserver {
    +update(event,data)
  }
  EventDispatcher --> EventObserver
  EventObserver <|.. NotificationObserver
  EventObserver <|.. AuditObserver

SECURE CODING - TWO MAIN THREATS
Threat 1: SQL Injection
- Mitigation: Doctrine ORM parameterisation / no user-built SQL in controllers.
- DB credentials are separated in config.
- Input validation and allowlists are used.

Threat 2: XSS / malicious input
- Mitigation: Security::e() escapes HTML output.
- Text lengths are limited.
- Role/ownership checks protect data access.

Additional:
- CSRF token on state-changing forms.
- password_hash/password_verify.
- session_regenerate_id after login.
- role-based access control.
- randomized upload file names.
- server-side MIME validation.
- upload size limit.
- uploads folder blocks PHP execution.
- controlled collection status transitions.

WEB SERVICES
Exposure:
  api.php

Services:
  collection.status
  notification.create
  dashboard.stats

Consumption:
- Module 4 dashboard uses fetch() to call dashboard.stats.
- Module 2 assignment uses InternalApiClient to call notification.create.
- Module 5 notifications page calls collection.status.

Bidirectional:
  Module 2 -> Module 5:
    notification.create
  Module 5 -> Module 2:
    collection.status

IFA:
Request:
{
  "requestID": "...",
  "timestamp": "...",
  "service": "collection.status",
  "payload": {...}
}

Response:
{
  "requestID": "...",
  "timestamp": "...",
  "status": "SUCCESS",
  "data": {...}
}

Robustness:
- shared service token
- JSON validation
- errors return ERROR status
- API client timeouts
- unknown services rejected
- missing IFA fields rejected

10) EMAIL
---------
By default SMTP is OFF so the project works immediately on XAMPP.
Verification/reset "emails" are stored in:
  storage/mail.log

For a live SMTP demo, edit config/app.php:
  mail.enabled = true
  username/password = your SMTP credentials

11) FINAL TEST ORDER
--------------------
A. Resident:
- login
- submit waste report
- select location on map
- request collection
- submit recycling
- book recycling appointment

B. Admin:
- user management
- suspend/activate account
- assign collection staff
- open dashboard
- monthly/annual reports
- create announcement
- view audit/activity logs

C. Collection Staff:
- view assigned collection
- Assigned -> In Progress -> Completed

D. Resident:
- see status/history and notifications

E. Recycling Operator:
- maintain recycling centre availability
- approve submission
- reward points generated
- update appointment status

F. Resident:
- see reward balance/history and recycling notifications

12) IMPORTANT
-------------
This is a strong integrated starter, but your team should:
- use your own UI styling/screenshots,
- add your names/module ownership,
- update class diagram to match final entities,
- test every path on your own XAMPP,
- change service token,
- do not claim SMTP email delivery unless you actually enable/test SMTP.


13) MODULE 1 RBAC - CLIENT VS ADMIN INTERFACE
--------------------------------------------
The website now has role-specific interfaces.

RESIDENT / CLIENT
- Home
- Waste Collection
- Recycling & Rewards
- Notifications
- My Account
- Can update own profile only
- Cannot manage other user accounts
- Cannot access admin pages

ADMIN
- Admin Home
- User Management
  * search/filter users
  * create user
  * edit user name
  * change role
  * activate/suspend account
- Collection Operations
- Analytics & Reports
- System Administration

COLLECTION STAFF
- My Collection Jobs
- Notifications
- My Account
- Can update only collections assigned to their own account

RECYCLING CENTER OPERATOR
- Centre Operations
- Notifications
- My Account
- Maintains centre data and reviews recycling activities

RBAC is enforced on BOTH levels:
1. Client/UI level: navigation and screens differ according to role.
2. Server level: Security::requireRole() prevents direct URL access.

This is important: hiding a button alone is NOT security.
The server-side role check is the actual authorization control.
