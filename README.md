KINGSLEY AGUAGWA
MSc Software Engineering with Management Studies



### README: Installing a PHP Program Developed With XAMPP

-
 Introduction:

This post explains how to set up a PHP program with XAMPP. The program's database is called 'IMS.sql'. In addition, a recording is available to help you understand how the system works.

---

Prerequisites

1. Install XAMPP from [https://www.apachefriends.org/].
2. **Program Files**: Make sure you have both the PHP program files and the 'IMS.sql' database file.
3. **Recording**: If necessary, use the recording to walk through the system.

---

Installation Steps

1. Install XAMPP: Download and install XAMPP to your PC.
   - Open XAMPP Control Panel and start the 
Apache and 
MySQL services.

2. Setup the Project:
   - Copy the PHP program folder to your XAMPP installation's 'htdocs' directory (for example, 'C:\xampp\htdocs\').
   - Rename the folder as required (for example, 'ims').

3. **Import the Database**: - Open your browser and navigate to [http://localhost/phpmyadmin].
   - Click on the **Databases** tab.
   - Create a new database called 'IMS'.
   - Import the 'IMS.sql' file.
     1. Choose the 'IMS' database.
     2. Click the **Import** tab.
     3. Upload the 'IMS.sql' file and click **Go**.

4.  Configure the Application:
   - Find the database configuration file (e.g., 'config.php') in the project folder.
   - Update the database credentials as necessary (the default XAMPP credentials are):
     '''php
     $servername = "localhost"; $username = "root"; $password = ""; $databasename = "IMS"; '''

5. Run the App:
   - Open your web browser and go to [http://localhost/ims](http://localhost/ims/public/login.php) 
   - The system should now be available.

---

#### Additional notes

- A full walkthrough of system features is provided in the recording.
- To debug, confirm that the Apache and MySQL services are running.

---

### Screenshots

![Screenshot of patient Registration](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(238).png?raw=true)
Patient Registration

![Screenshot of Login Page](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(239).png?raw=true)
Login Page

![Screenshot of Radiologist dashboard](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(240).png?raw=true)
Radiologist dashboard

![Screenshot of Create image category and subcategories](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(241).png?raw=true)
Create image categories and subcategories

![Screenshot of All Patient Record](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(242).png?raw=true)
All Patient Record

![Screenshot of Patient Record Details](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(243).png?raw=true)
Patient Record Details

![Screenshot of Request RRadiology Test](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(244).png?raw=true)
Request Radiology Test

![Screenshot of Radiology Request](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(245).png?raw=true)
Radiology Request

![Screenshot of Radiology Request Details](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(246).png?raw=true)
Radiology Request Details

![Screenshot of All Requested Tests](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(247).png?raw=true)
All Requested Tests

![Screenshot of Upload Radiology Test](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(248).png?raw=true)
Upload Radiology Test

![Screenshot of All Uploaded Images](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(249).png?raw=true)
All Uploaded Images 

![Screenshot of currency settings](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(250).png?raw=true)
Currency Settings

![Screenshot of Staff Registration](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(251).png?raw=true)
Staff Registration 

![Screenshot of Patient Dashboard](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(253).png?raw=true)
Patient Dashboard

![Screenshot of Patient invoice](https://github.com/ugosonic/Image_management_system/blob/main/screenshots/Screenshot%20(254).png?raw=true)
Patient invoice
