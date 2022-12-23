# eCommerce Reseller Website

# Installation
### Requirements
* PHP version >=7.4
* Shell access

### clone project
```shell
 cd /home/user
 sudo -u user git clone https://github.com/camoo/e-reseller.git public_html
 cd public_html
 sudo -u user composer install --no-dev
```

### Clear Cache
```shell
 cd /home/user/public_html/
 # clear all cache types
 sudo -u user ./bin/camoo cleanup:all
 # clear only template cache
 sudo -u user ./bin/camoo cleanup:tpl
```

### Customisation
It's possible to fully customize the project. All you need to do is:

#### ADD Custom CSS
Upload a file with the name `custom.css` into `/home/user/public_html/web/css/`

#### ADD Custom JS
Upload a file with the name `custom.js` into `/home/user/public_html/web/js/`

#### Change logo and favicon
* Set different name as `logo.png` or `favicon.ico`
* Upload first you files into `/home/user/public_html/web/img/`
```shell
# edit .env file and replace the following lines
# WEB
LOGO_FILE_NAME="my-site-log.png"
FAVICON_FILE_NAME="my-site-favicon.ico"
```

# Troubleshooting
in case that you site is displaying only Error.
Please make sure that:
* You add the ip-address of your server to our authorized list. To do so you have to login to your dashboard then under the reseller menu find `API`
* Or check your `config/.env` and make sure that you credentials are correct.

# Examples
* Home
![Home page](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-53-48.png)

* Package example
![Packages](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-55-02.png)

* Purchase process
![Purchase Package](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-55-44.png)

* Payment methods
![Payment method](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-56-26.png)

_Powered by Camoo.Hosting_
