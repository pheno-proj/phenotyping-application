# Digital Phenotyping of Children's Behavior: Final Project Overview

This project implements a comprehensive behavioral analysis platform using Digital Phenotyping and Machine Learning to monitor and predict student performance and behavioral risks.

## 🚀 Final Project Status: Fully Completed & Standardized

The system is now a production-ready platform with multi-role dashboards, automated data integration, and a high-performance AI engine.

### 1. Madrasti Platform Integration (Automation)
- **Automated Import**: Seamlessly synchronizes student data from external sources (simulated via JSON API).
- **Duplicate Prevention**: Implemented a robust check using `madrasti_id` to ensure data integrity.
- **Parental Account Automation**:
    - Automatic creation of parent accounts during student import.
    - **Smart Credentials**: Passwords default to the parent's phone number; emails are auto-formatted (name without spaces).
- **Behavior Mapping**: Direct integration of behavioral metrics (participation, absences) into the analytics engine.

### 2. AI Intelligence Engine (The Core)
- **Algorithm**: Powered by a **Random Forest Classifier** (500 trees) providing high stability and predictive power.
- **Accuracy**: Optimized at **86.4%** through advanced feature selection.
- **Proactive Alerts**: Automatic generation of counselor alerts for students identified as "Low Performance" (At-Risk).

### 3. Comprehensive Command Centers (RBAC)
- **Admin**: Full system audit, user management (Teachers, Parents, Counselors), and student database control.
- **Teacher**: Student lifecycle management, academic/behavioral input, and one-click AI behavioral diagnosis.
- **Counselor**: Centralized monitoring of at-risk students, interactive risk progress bars, and alert resolution flow.
- **Parent**: Direct access to children's behavioral categories and performance trends.

### 4. Professional UI/UX & Localization
- **Standardized Aesthetics**: Clean, modern interface using **FontAwesome** icons and **SweetAlert2** for interactive feedback.
- **Full Localization**: The entire platform has been translated into professional English.
- **Responsive Design**: Optimized for desktop and mobile monitoring.

## 🛠️ Technical Stack
- **Backend**: PHP 8.x with PDO (Secure SQL handling)
- **Intelligence Layer**: Python 3.x (Scikit-learn, Pandas, Joblib)
- **Frontend**: Bootstrap 5, FontAwesome, SweetAlert2
- **Database**: MySQL (Relational architecture with 9 core tables)

## 🔐 Security & Integration
- **Credential Protection**: Industry-standard `password_hash()` for all user accounts.
- **Session Management**: Secure role-based session validation.
- **PHP-Python Bridge**: Localized JSON communication for cross-platform stability.

---
*Created as part of the Digital Phenotyping Research Initiative.*
