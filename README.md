<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->
<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/github_username/repo_name">
    <img src="images/logo.png" alt="Logo" width="80" height="80">
  </a>

<h3 align="center">PaL Server Info</h3>

  <p align="center">
    Php And Laravel (PaL) Server Info
  </p>
</div>

<!-- ABOUT THE PROJECT -->
## About The Project

Php And Laravel (PaL) Server Info
Also this tool performs a benchmark test on MySQL database and PHP server.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



### Built With

* Php
* Bootstrap 5.3
* Jquery 3.6

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

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

### Mysql Benchmark Config

1. Don't forget to enter the mysql username and password in 'Pal-Server-Info.php' on line 22
   ```php
      $MYSQL_CONFIG = [
        'host' => 'localhost',
        'username' => 'USER_NAME_HERE', //ex : root
        'password' => 'PASSWORD_HERE', //ex : password
        'db' => 'DB_NAME_HERE',         //ex : laravel_db
        'benchmark_insert' => 100,      //ex : 100
      ];
   ```
2. Refresh Your Browser


<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTACT -->
## Contact

Saeed Abdollahian - [@PhpWebDeveloper]([https://twitter.com/twitter_handle](https://t.me/PhpWebDeveloper)) - saeed.es91@gmail.com

Project Link: [https://github.com/saeedvir/PaL-Server-Info](https://github.com/saeedvir/PaL-Server-Info)

<p align="right">(<a href="#readme-top">back to top</a>)</p>
