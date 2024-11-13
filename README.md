# Time Tracker Application

This application is a Time Tracking System designed to manage employee attendance and work hours. It is built using PHP Desktop and SQLite for data management. The system includes both an administrator dashboard and employee interface, with functionalities for authentication, user management, and reporting. The application also focuses on security to ensure data privacy and integrity.

## Features

### 1. Authentication System
   - User login and registration for employees and administrators.
   - Secure password hashing and verification using `password_hash()` and `password_verify()`.

### 2. Administrator Dashboard
   - **User Management**: Administrators can add, modify, and delete employee information.
   - **Report Generation**: Generate detailed reports on employee working hours, which can be exported as PDF.
   - **Access Control**: Only authorized users can access the administrator panel.

#### Database Structure
The database manages data related to employee information, work hours, and authentication details.

### 3. Employee Dashboard
   - **Clock In/Out**: Employees can log their working hours with a one-click system to track their start and end times.
   - **Profile Management**: Employees can view and update their personal information.
   - **Shift Details**: Employees can review their worked hours within a specified period.

## Detailed Functionality

### 1.1 Admin Pages

#### 1.1.1 Login Page
   - Allows administrators and employees to log in to the system securely.

#### 1.1.2 Admin Home Page
   - Provides a dashboard with options for managing employee records, viewing reports, and accessing system settings.

#### 1.1.3 Employee Account Creation Page
   - Enables administrators to add new employees with details like username, role, and contact information.

#### 1.1.4 Employee Report Page
   - Generates reports for specific employees within a specified date range, with sections for:
     - **User Information**: Basic details like ID, username, and role.
     - **Shifts Chart**: A visual chart showing hours worked.
     - **Shifts Table**: Tabular view of start and end times for each workday.
     - **Calendar View**: Visual calendar to track worked and non-worked days (green for worked, red for non-worked, and blue for the current day).
   - The report can be exported as a PDF for offline use or record-keeping.

### 1.2 Employee Pages

#### 1.2.1 Employee Home Page
   - Displays a welcome message, the current time, and a "Clock In" button for starting the work shift.

#### 1.2.2 Employee Profile Page
   - Allows employees to view and edit personal information like name, email, and password.

#### 1.2.3 Shift Management
   - Provides a section for employees to view their clock-in and clock-out times for each shift.

## Security Features

1. **Database Connection Security**: Uses `mysqli` with secure configurations to prevent unauthorized access.
2. **Password Hashing and Verification**: Uses `password_hash()` and `password_verify()` to securely store and check passwords.
3. **Session Management**: Utilizes `session_start()` to manage user sessions securely.
4. **Access Control**: Ensures only authenticated users can access sensitive data or perform admin actions.
5. **SQL Injection Prevention**: Implements prepared statements and parameterized queries to prevent SQL injection attacks.
6. **HTTPS Protocol**: Secures all data transmission between client and server to prevent man-in-the-middle attacks.

## Requirements

- PHP Desktop
- SQLite
- A web server or local server setup for running PHP applications

## Installation

1. Clone this repository:
   ```bash
   git clone https://github.com/notAnKy/time-tracker-application.git
2.Install PHP Desktop and configure the application to use SQLite.
3.Set up the database according to the provided schema (refer to Figure 10: Database).
4.Start the application, and access it through PHP Desktop or your configured server environment.
## Usage
1. Administrator: Log in to manage employee data, generate reports, and access administrative settings.
2. Employee: Log in to clock in/out, view shifts, and manage personal profile information.
## Future Improvements
. Enhanced reporting features with additional filtering options.
. Integration with a cloud database for distributed data access.
. Expanded security with multi-factor authentication.
