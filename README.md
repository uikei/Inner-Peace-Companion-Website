# Inner Peace Companion Website

**DIP2008 IT Mini Project**

A comprehensive mental health companion web application that provides mental health screening, mood tracking, AI-powered therapy chatbot, relaxation activities, and focus mode features.

---

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Features Overview](#-features-overview)
- [API Integration](#-api-integration)
- [Contributing](#-contributing)

---

## ✨ Features

- **User Authentication**: Secure login and registration system with password reset functionality
- **Mental Health Screening**: PHQ-9 (Depression) and GAD-7 (Anxiety) assessment tools
- **Mood Diary**: Track daily moods and journal entries
- **AI Therapy Chatbot**: Powered by Claude API for supportive mental health conversations
- **User Trends Analysis**: Visualize mental health trends over time
- **Relax Mode**: Guided relaxation exercises and breathing techniques
- **Focus Mode**: Concentration tools and productivity features
- **Report Generation**: Download comprehensive PDF reports of mental health assessments
- **Profile Management**: Edit and update user account information

---

## 🛠 Technology Stack

**Frontend:**
- HTML5, CSS3, JavaScript
- Responsive design for mobile and desktop

**Backend:**
- PHP 7.4+
- PDO/MySQLi for database operations
- RESTful API architecture

**Database:**
- MySQL/MariaDB

**APIs:**
- Claude API (Anthropic) for AI chatbot functionality

**Server:**
- MAMP/XAMPP/LAMP compatible
- Apache Web Server

---

## 📁 Project Structure

```
Mental-Health-Companion-Website/
├── backend/
│   ├── api_chatbot.php              # Chatbot API endpoint
│   ├── api_usertrend_analysis.php   # User trend analysis API
│   ├── config_chatbot.php           # Chatbot configuration
│   ├── config_diary.php             # Diary database config
│   ├── config_mentalhealth.php      # Mental health screening config
│   ├── config_usertrend.php         # User trend configuration
│   ├── database.php                 # Main database connection
│   ├── diary_handler.php            # Diary CRUD operations
│   ├── Login.php                    # User login handler
│   ├── SignUp.php                   # User registration handler
│   ├── ForgotUsernameProcess.php    # Username recovery
│   ├── ResetPassword.php            # Password reset handler
│   ├── submitPHQ.php                # PHQ-9 submission handler
│   ├── submitGAD.php                # GAD-7 submission handler
│   └── download_report_pdf.php      # PDF report generator
│
├── frontend/
│   ├── home.php                     # Main dashboard
│   ├── Login.html                   # Login page
│   ├── SignUp.html                  # Registration page
│   ├── LandingPage.html             # Welcome/landing page
│   ├── chatbot.php                  # AI therapy chatbot interface
│   ├── diary.php                    # Mood diary interface
│   ├── MHScreening.php              # Mental health screening
│   ├── report.php                   # Assessment reports
│   ├── user_trend.php               # Trend visualization
│   ├── RelaxMode.php                # Relaxation exercises
│   ├── Focus Mode.php               # Focus/productivity mode
│   ├── editProfile.php              # Profile editing
│   ├── Setting.php                  # User settings
│   ├── header.php                   # Navigation header
│   └── sidebar.php                  # Sidebar navigation
│
├── db/                              # Database files
├── uploads/                         # User uploaded files
├── src/                             # Additional source files
├── tools/                           # Utility tools
├── instruction_chatbot.txt          # Chatbot AI instructions
├── instruction_report.txt           # Report generation instructions
├── instruction_usertrend.txt        # User trend instructions
├── .env                             # Environment variables (API keys)
└── README.md                        # This file
```

---

## 🚀 Installation

### Prerequisites

- **MAMP/XAMPP/LAMP** server environment
- **PHP 7.4** or higher
- **MySQL 5.7** or higher
- **Composer** (optional, for dependency management)
- **Claude API Key** from Anthropic

### Step-by-Step Guide

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd Mental-Health-Companion-Website
   ```

2. **Move to MAMP/XAMPP htdocs**
   ```bash
   # For MAMP
   mv Mental-Health-Companion-Website /Applications/MAMP/htdocs/
   
   # For XAMPP (Windows)
   # Move folder to C:\xampp\htdocs\
   ```

3. **Start Your Server**
   - Start MAMP/XAMPP
   - Ensure Apache and MySQL are running
   - Default ports: Apache (80/8888), MySQL (3306/8889)

---

## 💾 Database Setup

### Create Database

1. **Access phpMyAdmin**
   - URL: `http://localhost/phpMyAdmin` or `http://localhost:8888/phpMyAdmin`

2. **Create New Database**
   ```sql
   CREATE DATABASE innerpeacecomp_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import Database Schema**
   - Navigate to the `db/` directory in the project
   - Import the SQL file(s) into the `innerpeacecomp_web` database
   - Or create tables manually using the schema provided

### Required Tables

The application requires the following tables:
- `users` - User authentication and profiles
- `diary_entries` - Mood diary entries
- `chat_sessions` - Chatbot conversation sessions
- `chat_messages` - Chatbot messages
- `phq9_results` - PHQ-9 assessment results
- `gad7_results` - GAD-7 assessment results
- `screening_reminders` - Mental health screening reminders

---

## ⚙️ Configuration

### 1. Database Configuration

**Default Configuration (MAMP):**
- Host: `localhost`
- Username: `root`
- Password: `` (empty)
- Database: `innerpeacecomp_web`
- Port: `3306` (or `8889` for MAMP)

All database configuration files have been set to use these defaults. If your setup differs, update the following files:

```php
// Files to update:
backend/database.php
backend/config_diary.php
backend/config_chatbot.php
backend/config_mentalhealth.php
backend/Login.php
backend/SignUp.php
frontend/Edit_Account.php
frontend/Update_Account.php
```

### 2. API Configuration (Claude AI)

1. **Get API Key from Anthropic**
   - Visit: https://console.anthropic.com/
   - Create an account and generate an API key

2. **Create `.env` File**
   ```bash
   # In the project root directory
   touch .env
   ```

3. **Add API Key to `.env`**
   ```env
   API_KEY=your_claude_api_key_here
   ```

4. **Important**: The `.env` file is gitignored for security. Never commit API keys!

### 3. Port Configuration (For MAMP Users)

If using MAMP with port 8889, uncomment this line in `backend/database.php`:
```php
$port = '8889';
```

---

## 📖 Usage

### Accessing the Application

1. **Start your server** (MAMP/XAMPP)

2. **Open in browser:**
   ```
   http://localhost/Mental-Health-Companion-Website/frontend/LandingPage.html
   ```
   Or for MAMP:
   ```
   http://localhost:8888/Mental-Health-Companion-Website/frontend/LandingPage.html
   ```

### User Flow

1. **Landing Page** → Click "Get Started"
2. **Sign Up** → Create a new account
3. **Login** → Access your dashboard
4. **Home Dashboard** → Navigate to different features:
   - Mental Health Screening
   - Mood Diary
   - AI Chatbot
   - Reports & Trends
   - Relax Mode
   - Focus Mode

---

## 🎯 Features Overview

### 1. Mental Health Screening
- **PHQ-9**: Depression screening questionnaire
- **GAD-7**: Anxiety assessment tool
- **Results**: Immediate feedback with severity levels
- **Tracking**: Historical data storage for trend analysis

### 2. Mood Diary
- **Daily Entries**: Record mood and thoughts
- **Mood Tracking**: Visual indicators for different emotions
- **Journal**: Free-form text entries
- **History**: View and edit past entries

### 3. AI Therapy Chatbot
- **Conversational AI**: Powered by Claude 3.7 Sonnet
- **Empathetic Responses**: Trained on therapy best practices
- **Session Management**: Maintains conversation context
- **Privacy**: Secure message storage

### 4. User Trends & Reports
- **Visual Charts**: Mood and mental health trends over time
- **PDF Reports**: Downloadable comprehensive assessments
- **Progress Tracking**: Monitor mental health journey
- **Data Insights**: AI-powered trend analysis

### 5. Relaxation Features
- **Guided Breathing**: Breathing exercise animations
- **Meditation Tools**: Timer and guided sessions
- **Calming Content**: Relaxation techniques

### 6. Focus Mode
- **Pomodoro Timer**: Productivity time management
- **Distraction Blocking**: Focused work environment
- **Task Management**: Track focus sessions

---

## 🔌 API Integration

### Claude AI Chatbot

The chatbot uses the Anthropic Claude API:

**Configuration:**
- Model: `claude-3-7-sonnet-20250219`
- Endpoint: `https://api.anthropic.com/v1/messages`
- Instructions: Loaded from `instruction_chatbot.txt`

**Custom Instructions:**
Edit `instruction_chatbot.txt` to customize the chatbot's behavior and therapy approach.

### API Endpoints

**Internal API Routes:**
- `POST /backend/api_chatbot.php` - Chatbot messages
- `POST /backend/api_usertrend_analysis.php` - Trend analysis
- `POST /backend/diary_handler.php` - Diary CRUD operations
- `POST /backend/submitPHQ.php` - Submit PHQ-9 assessment
- `POST /backend/submitGAD.php` - Submit GAD-7 assessment
- `GET /backend/download_report_pdf.php` - Generate PDF report

---

## 🔒 Security Notes

- **Password Storage**: Passwords are hashed using PHP's `password_hash()`
- **SQL Injection**: PDO prepared statements used throughout
- **Session Management**: Secure PHP sessions with httponly cookies
- **Environment Variables**: Sensitive data in `.env` (gitignored)
- **Input Validation**: Server-side validation on all user inputs

---

## 🐛 Troubleshooting

### Database Connection Issues
```
Error: Connection failed
```
**Solution:**
- Verify MySQL is running
- Check database credentials in config files
- Ensure `innerpeacecomp_web` database exists
- Check port numbers (3306 or 8889)

### Chatbot Not Working
```
Error: API request failed
```
**Solution:**
- Verify `.env` file exists with valid API key
- Check Claude API key is active
- Ensure sufficient API credits
- Check `instruction_chatbot.txt` exists

### Pages Not Loading
```
404 Not Found or Blank Pages
```
**Solution:**
- Verify Apache is running
- Check file paths in URLs
- Ensure `.php` files have proper permissions
- Check Apache error logs

---

## 📝 Development

### Adding New Features

1. **Backend**: Add new PHP files in `/backend/`
2. **Frontend**: Add new pages in `/frontend/`
3. **Database**: Update schema and migration files in `/db/`
4. **Configuration**: Update relevant config files

### Code Style

- Use PSR-12 coding standard for PHP
- Consistent indentation (4 spaces)
- Meaningful variable and function names
- Comments for complex logic
- Security-first approach

---

## 👥 Contributing

This is an academic project for DIP2008 IT Mini Project. Contributions, suggestions, and improvements are welcome!

---

## 📄 License

This project is created for educational purposes as part of the DIP2008 IT Mini Project.

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review configuration settings
3. Verify database connection
4. Check server logs

---

## 🙏 Acknowledgments

- **Anthropic** for Claude AI API
- **Mental Health Resources** for assessment tools (PHQ-9, GAD-7)
- **Community** for open-source inspiration

---

**Last Updated:** December 2025

**Project Status:** Active Development
