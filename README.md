# Intro

Evals are to be respected! **NEVER** run this on anything publically available! This is the keys to the castle for PHP. Anything PHP can do, eval will interperet it. Only warning...

## Commands

-   Save `Ctrl`+`S`
-   Last `Ctrl`+`R`
-   Evaluate `Ctrl`+`Enter`
-   Fresh Start `Ctrl`+`B`

## Text Wrapping

-   Wrap highlighted text with brackets, braces, parethesis, backticks, single and double quotes, as well as greater\less than

## Setup on Windows

-   Install [XAMPP](https://www.apachefriends.org/)
-   Modify `httpd.conf`
    Default Location: `C:\xampp\apache\conf\httpd.conf`
    Search for `DocumentRoot` and replace it with the following

```
# Replace "LOCATION" with location of your project folder
DocumentRoot "LOCATION"
<Directory "LOCATION">
     Options Indexes FollowSymLinks Includes ExecCGI
     AllowOverride All
     Require all granted
</Directory>
```

-   Restart Apache Service
-   Download EvalPal to your project folder
-   Go to location, example: `http://localhost/EvalPal/`

## Setup on WSL2 Ubuntu 20.04

-   Install Apache2
    -   `sudo apt update`
    -   `sudo apt install apache2`
    -   `sudo ufw allow 'Apache Full'`(allows port 80 and 443)
-   `sudo mkdir -p -v /home/USER/dev/EvalPal`
-   `cd /home/USER/dev/EvalPal`
-   `git clone https://github.com/ImSeaWorld/EvalPal /home/USER/dev/EvalPal`
-   Make `saved` folder to save scripts
    -   `sudo mkdir -p -v /home/USER/dev/EvalPal/saved/`
    -   `sudo chown www-data -R /home/USER/dev/EvalPal/saved/`
-   `sudo nano /etc/apache2/sites-available/000-default.conf` or `vim` if you prefer.
    -   Go to(`ctrl`+`shift`+`_`) line 12 to `DocumentRoot /var/www/public`
    -   Exit(`ctrl`+`x`) and save
-   `ln -s /home/USER/dev/EvalPal /var/www/public`
-   `sudo service apache2 restart`
-   Now visit [http://localhost/EvalPal](http://localhost/EvalPal) and you should see EvalPal.

## Future Features/Ideas

-   MySQL eval
-   JavaScript eval(dev tools exists though)

## Example Usage

#### Test project for errors before production

![](https://i.imgur.com/xXzBszN.png)

#### Easily do all the php things

![](https://i.imgur.com/upDFakx.png)
