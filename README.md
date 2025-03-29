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

![Screenshot of patient Registration](https://github.com/ugosonic/Image_management_system/blob/main/Screenshot%20(238).png?raw=true)
Patient Registration

![Screenshot of Login Page](https://github.com/ugosonic/Image_management_system/screenshot/blob/main/Screenshot%20(239).png?raw=true)
Login Page

![Screenshot of Radiologist dashboard](https://github.com/ugosonic/Image_management_system/screenshot/blob/main/Screenshot%20(240).png?raw=true)
Radiologist dashboard




