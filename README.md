<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->
<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  
<h3 align="center">PaL Server Info</h3>

  <p align="center">
    PHP And Laravel (PaL) Server Info And Laravel Requirements Checker + PHP And Mysql Benchmark + Scan PHP Configuration in single file !!
  </p>
</div>

<!-- ABOUT THE PROJECT -->
## About The Project

![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-1.png)


Php And Laravel (PaL) Server Info
Also this tool performs a benchmark test on MySQL database and PHP server.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<hr><br>
<h4>
  <a href="https://vrgl.ir/dykF4">راهنمای فارسی معرفی و نصب اسکریپت PaL را اینجا بخوانید</a>
</h4>

<img src="https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/donate-fa.jpg" width="100%">

<h4>
<a href="https://reymit.ir/saeedvir">برای حمایت مالی پروژه PaL اینجا کلیک کنید</a>
</h4>
<br>

<hr>

### Features
* Check Laravel Requirements (5.8,6.x,7.x,8.x,9.x,10.x,11.x supported !)
* Php Config Information
* Check Php Config
* Scan Web Server Headers
* Php Benchmark
* Mysql Benchmark
* PHP INI Editor (in web ui only)
* CLI mode

### FILES
* <em>Web UI</em> : <b>Pal-Server-Info.php</b>
* <em>CLI (command line)</em> : <b>PaL-cli.php</b>
### Built With

* Php
* Bootstrap 5.3
* Jquery 3.6

<p align="right">(<a href="#readme-top">back to top</a>)</p>

# Download

[Download Latest Releases](https://github.com/saeedvir/PaL-Server-Info/releases)

<hr>

<!-- GETTING STARTED -->
## Getting Started

download "[Pal-Server-Info.php](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/Pal-Server-Info.php)"

Just copy the file to your server or host and call it.

### Example

  ```sh
  http://Your-web-address.com/Pal-Server-Info.php
  ```

To run it on localhost, just call the following command or copy it to your web server folder

  ```sh
  php -S localhost:8000

  http://localhost:8000/Pal-Server-Info.php
  ```
Or
  ```sh
  http://127.0.0.1/Pal-Server-Info.php
  ```

### Php Scan Configuration

![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-4.png)

### Php INI Editor

![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-8.png)

### Web Server Headers Scanner

![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-7.png)

### Php And Mysql Benchmark Config

![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-2.png)
![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-3.png)

### MySQL Config
1. Don't forget to enter the mysql username and password
![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-9.png)

or in 'Pal-Server-Info.php' on line 27
   ```php
      $MYSQL_CONFIG = [
        'host' => 'localhost',
        'username' => 'USER_NAME_HERE', //ex : root
        'password' => 'PASSWORD_HERE', //ex : password
        'db' => 'DB_NAME_HERE',         //ex : laravel_db
        'benchmark_insert' => 100,      //ex : 100
      ];
   ```
3. Refresh Your Browser

## How To Use CLI Mode?
Download the "PaL-cli.php" file
and use :
  ```sh
  php PaL-cli.php help
  php PaL-cli.php -i -s -o -r
  ```

![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-5.png)
![Alt text](https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/img/image-6.png)

## How To Customize PHP Configuration Scan
Download the "pal-config.json" file
You can edit this file

## How To Update
Just click on "Check for Update" at the bottom of the page (footer)

in cli you can use :
```sh
php PaL-cli.php up
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTACT -->
## Contact

Telegram:
Saeed Abdollahian - [@PhpWebDeveloper]([https://t.me/PhpWebDeveloper](https://t.me/PhpWebDeveloper)) - saeed.es91@gmail.com

Project Link: [https://github.com/saeedvir/PaL-Server-Info](https://github.com/saeedvir/PaL-Server-Info)

<p align="right">(<a href="#readme-top">back to top</a>)</p>
